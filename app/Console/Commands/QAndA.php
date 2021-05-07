<?php

namespace App\Console\Commands;

use App\Console\Commands\Contracts\AnswerInterface;
use App\Console\Commands\Contracts\OptionInterface;
use App\Console\Commands\Contracts\QuestionInterface;
use App\Console\Commands\Contracts\ResponseInterface;
use App\Console\Commands\Services\InputReaderService;
use App\Console\Commands\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Whoops\Exception\ErrorException;

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
                if (!empty($question) && $question !== Lang::get('qanda.exit')) {
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

                $question = $this->inputReader->chooseYourQuestion(
                    Lang::get('qanda.question_to_practice'),
                    $questions->toArray()
                );

                if ($question === Lang::get('exit')) {
                    break;
                }

                if ($question !== Lang::get('exit')) {
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

    /**
     * @return array
     */
    protected function getInitializedVar(): array
    {
        $answer = null;
        $qAndA = [];
        return [$answer, $qAndA];
    }

    /**
     * @param string $answer
     * @param $qAndA
     * @param string $question
     * @return array
     */
    protected function getAnsweredQuestions(string $answer, $qAndA, string $question): array
    {
        $answers = explode(', ', trim($answer));
        $qAndA[$question][] = $answers;
        return [$answers, $qAndA];
    }

    /**
     * @param $answers
     * @param $qAndA
     * @param array|null $question
     * @return mixed
     */
    protected function getChosenAnswers($answers, $qAndA, string $question)
    {
        $chosenAnswer = $this->inputReader->chooseAnAnswer($answers);
        $qAndA[$question][] = $chosenAnswer;
        return $qAndA;
    }

    /**
     * @param array $qAndA
     * @throws \Exception
     */
    protected function InsertQAndA(array $qAndA): void
    {
        try {
            DB::beginTransaction();
            $this->question->insert($this->question->prepareData(($qAndA)));
            $this->option->insert($this->option->prepareData($qAndA));
            $this->answer->insert($this->answer->prepareData($qAndA));
            DB::commit();
        } catch (ErrorException $exception) {
            DB::rollBack();
            throw new $exception;
        }
    }

    /**
     * @param string $question
     * @return array
     */
    protected function getChosenQuestion(string $question): array
    {
        $chosenQuestion = $this->question->model()::OfWhereQuestion($question)->first();
        $answerProvided = $this->inputReader->chooseYourQuestion(
            $chosenQuestion->question,
            $chosenQuestion->options->pluck('option')->toArray()
        );
        return [$chosenQuestion, $answerProvided];
    }

    /**
     * @param string $question
     * @return bool
     */
    protected function readyToExit(string $question): bool
    {
        return $question === Lang::get('qanda.exit') ||
            $question === strtolower(Lang::get('qanda.exit'));
    }
}
