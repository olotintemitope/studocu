<?php

namespace App\Console\Commands;

use App\Console\Commands\Contracts\AnswerInterface;
use App\Console\Commands\Contracts\OptionInterface;
use App\Console\Commands\Contracts\QuestionInterface;
use App\Console\Commands\Contracts\ResponseInterface;
use App\Console\Commands\Services\InputReaderService;
use App\Console\Commands\Services\ReportService;
use App\Console\Commands\Traits\QAndATraits;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;

class QAndA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qanda:interactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs an interactive command line based Q And A system.';
    /**
     * @var InputReaderService
     */
    private $inputReader;
    /**
     * @var QuestionInterface
     */
    private $question;
    /**
     * @var OptionInterface
     */
    private $option;
    /**
     * @var AnswerInterface
     */
    private $answer;
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var ReportService
     */
    private $summary;

    /**
     * Create a new command instance.
     * @param InputReaderService $inputReader
     * @param QuestionInterface $questionRepository
     * @param OptionInterface $optionRepository
     * @param AnswerInterface $answerRepository
     * @param ResponseInterface $responseRepository
     * @param ReportService $report
     */
    public function __construct(
        InputReaderService $inputReader,
        QuestionInterface $questionRepository,
        OptionInterface $optionRepository,
        AnswerInterface $answerRepository,
        ResponseInterface $responseRepository,
        ReportService $report
    )
    {
        parent::__construct();

        $this->question = $questionRepository;
        $this->option = $optionRepository;
        $this->answer = $answerRepository;
        $this->response = $responseRepository;

        $this->inputReader = new $inputReader($this);
        $this->summary = new $report($responseRepository, $questionRepository, $this);
    }

    use QAndATraits;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info(Lang::get('qanda.welcome_msg'));
        // Create your interactive Q And A system here. Be sure to make use of all of Laravels functionalities.
        $chosenOption = $this->inputReader->chooseOption();
        if ($chosenOption === Lang::get('qanda.exit')) {
           return $this->info(Lang::get('qanda.bye'));
        }

        if ($chosenOption === Lang::get('qanda.question.opt_1')) {
            [$answer, $qAndA] = $this->getInitializedVar();
            do {
                $question = $this->inputReader->askQuestion();
                if ($this->readyToExit($question)) {
                    break;
                }
                if ($this->userWillNotExit($question)) {
                    $qAndA[$question] = [];
                    $answer = $this->inputReader->provideAnAnswer();
                }
                if (!empty($answer)) {
                    [$answers, $qAndA] = $this->getAnsweredQuestions($answer, $qAndA, $question);
                    $qAndA = $this->getChosenAnswers($answers, $qAndA, $question);
                }
            } while (true);

            try {
                if (count($qAndA) > 0) {
                    $this->InsertQAndA($qAndA);
                    $this->info(Lang::get('qanda.insert_success_msg'));
                }
                $this->info(Lang::get('qanda.bye'));
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }

        if ($chosenOption === Lang::get('qanda.question.opt_2')) {
            do {
                $questions = $this->question->getUnansweredQuestions();
                if ($questions->count() <= 0) {
                    break;
                }

                $question = $this->inputReader->chooseAnyQuestion(
                    Lang::get('qanda.question_to_practice'),
                    $this->getQuestionsWithExitOption($questions)
                );

                if ($this->readyToExit($question)) {
                    break;
                }

                if (!$this->readyToExit($question)) {
                    [$chosenQuestion, $answerProvided] = $this->getChosenQuestion($question);
                    try {
                        $this->response->insert(
                            $this->response->prepareData([$chosenQuestion->id, $answerProvided])
                        );
                        $this->info(Lang::get('qanda.insert_success_msg'));

                        if ($this->question->getUnansweredQuestions()->count() > 0) {
                            $this->table($this->summary->getReportTableHeaders(), $this->summary->getCompletedQuestions());
                        }
                    } catch (\Exception $exception) {
                        $this->error($exception->getMessage());
                    }
                }
            } while (true);

            $this->summary->getReport();
        }
    }
}
