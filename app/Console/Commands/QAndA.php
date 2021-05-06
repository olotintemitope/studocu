<?php

namespace App\Console\Commands;

use App\Console\Commands\Contracts\AnswerInterface;
use App\Console\Commands\Contracts\OptionInterface;
use App\Console\Commands\Contracts\QuestionInterface;
use App\Console\Commands\Contracts\ResponseInterface;
use App\Console\Commands\Services\InputReaderService;
use App\Console\Commands\Services\ReportService;
use App\Models\Response;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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
     * @var QuestionRepository
     */
    private $question;
    /**
     * @var OptionRepository
     */
    private $option;
    /**
     * @var AnswerRepository
     */
    private $answer;
    /**
     * @var ResponseRepository
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
        $this->summary = new $report($responseRepository, $questionRepository);
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

        if ($chosenOption === Lang::get('qanda.question.opt_1')) {
            $answer = null;
            $chosenAnswer = null;
            $qAndA = [];

            do {
                $question = $this->inputReader->askQuestion();
                if ($question === Lang::get('qanda.exit')) {
                    break;
                }
                if (!empty($question) && $question !== Lang::get('qanda.exit')) {
                    $qAndA[$question] = [];
                    $answer = $this->inputReader->provideAnswers();
                }
                if (!empty($answer)) {
                    $answers = explode(', ', trim($answer));
                    $qAndA[$question][] = $answers;

                    $chosenAnswer = $this->inputReader->chooseAnAnswer($answers);
                    $qAndA[$question][] = $chosenAnswer;
                }
            } while (true);

            try {
                $this->InsertQAndA($qAndA);
                $this->info(Lang::get('qanda.insert_success_msg'));
            } catch (\RuntimeException $exception) {
                $this->error($exception->getMessage());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }

        if ($chosenOption === Lang::get('qanda.question.opt_2')) {
            do {
                $questions = $this->getUnansweredQuestions();
                if ($questions->count() <= 0) {
                    break;
                }

                $question = $this->inputReader->askMultipleChoiceQuestion(
                    Lang::get('qanda.question_to_practice'),
                    $questions->toArray()
                );

                if ($question === Lang::get('exit')) {
                    break;
                }

                if ($question !== Lang::get('exit')) {
                    $chosenQuestion = $this->question->model()::OfWhereQuestion($question)->first();
                    $answerProvided = $this->inputReader->askMultipleChoiceQuestion(
                        $chosenQuestion->question,
                        $chosenQuestion->options->pluck('option')->toArray()
                    );

                    try {
                        $this->response->insert(
                            $this->response->prepareData([$chosenQuestion->id, $answerProvided])
                        );
                        $this->info(Lang::get('qanda.insert_success_msg'));

                        if ($this->getUnansweredQuestions()->count() > 0) {
                            $this->table($this->getReportTableHeaders(), $this->getCompletedQuestions());
                        }
                    } catch (\Exception $exception) {
                        $this->error($exception->getMessage());
                    }
                }

            } while (true);

            $this->getReportSummary();
        }
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
     * @return Collection
     */
    protected function getUnansweredQuestions(): Collection
    {
        $answeredQuestions = $this->response->getAll()->pluck('question_id')->toArray();

        return $this->question->getAll()
            ->filter(static function ($question) use ($answeredQuestions) {
                return !in_array($question->id, $answeredQuestions, true);
            })
            ->pluck('question');
    }

    /**
     * @return string[]
     */
    protected function getReportTableHeaders(): array
    {
        return [
            'Question',
            'Your Answer',
            'Correct Answer',
            'Status'
        ];
    }

    /**
     * @return mixed
     */
    protected function getCompletedQuestions()
    {
        return $this->response->getAll()->reduce(function ($arr, Response $response) {
            $question = $response->question;
            $yourAnswer = $response->answer;
            $correctAnswer = $question->answer->answer;
            $arr[] = [
                $question->question,
                $yourAnswer,
                $correctAnswer,
                $yourAnswer === $correctAnswer ? '✓' : 'X',
            ];

            return $arr;
        }, []);
    }

    protected function getReportSummary(): void
    {
        $this->info(Lang::get('qanda.response_summary'));
        $this->table($this->getReportTableHeaders(), $this->getCompletedQuestions());

        $this->info(Lang::get('qanda.questions.completion_percentage', [
            'percentage' => $this->summary->getCompletedPercentage()
        ]));

        [$completed, $total] = $this->summary->getNoOfCompletedQuestions();
        $this->info(Lang::get('qanda.questions.total_completion', [
            'completed' => $completed,
            'total' => $total
        ]));

        $this->info(Lang::get('qanda.questions.correct_answer.percentage', [
            'percentage' => $this->summary->getPercentageOfCorrectAnswers()
        ]));

        [$rightAnswerCounts, $wrongAnswerCounts] = $this->summary->getNoOfCompletedRightAnswers();
        $this->info(Lang::get('qanda.questions.answers', [
            'rightAnswer' => $rightAnswerCounts,
            'wrongAnswer' => $wrongAnswerCounts
        ]));
    }
}
