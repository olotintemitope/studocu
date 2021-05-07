<?php


namespace App\Console\Commands\Services;

use App\Console\Commands\Repositories\QuestionRepository;
use App\Console\Commands\Repositories\ResponseRepository;
use App\Models\Response;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class ReportService
{
    const ZERO_VALUE = 0;
    /**
     * @var ResponseRepository
     */
    private $response;
    /**
     * @var QuestionRepository
     */
    private $question;
    /**
     * @var Command
     */
    private $console;

    public function __construct(ResponseRepository $response, QuestionRepository $question, Command $command)
    {
        $this->response = $response;
        $this->question = $question;
        $this->console = $command;
    }

    /**
     * @return int
     */
    public function getCompletedPercentage(): int
    {
        return ($this->response->getAll()->count() / $this->question->getAll()->count()) * 100;
    }

    /**
     * @return array
     */
    public function getNoOfCompletedQuestions(): array
    {
        return [
            $this->response->getAll()->count(),
            $this->question->getAll()->count(),
        ];
    }

    public function getPercentageOfCorrectAnswers(): int
    {
        $responses = $this->response->getAll();

        $answers = $this->getResponseAnswers($responses);

        return (
            $answers['right_answer'] > 0
                ? $answers['right_answer'] / $responses->count()
                : self::ZERO_VALUE
            ) * 100;
    }

    public function getNoOfCompletedRightAnswers(): array
    {
        $responses = $this->response->getAll();

        return array_values($this->getResponseAnswers($responses));
    }

    /**
     * @param Collection $responses
     * @return mixed
     */
    protected function getResponseAnswers(Collection $responses)
    {
        return $responses->reduce(function ($arr, Response $response) {
            $question = $response->question;
            $yourAnswer = $response->answer;
            $correctAnswer = $question->answer->answer;

            if ($yourAnswer === $correctAnswer) {
                $arr['right_answer'] += 1;
            }

            if ($yourAnswer !== $correctAnswer) {
                $arr['wrong_answer'] += 1;
            }

            return $arr;
        }, ['right_answer' => 0, 'wrong_answer' => 0]);
    }

    public function getReport(): void
    {
        $this->console->info(Lang::get('qanda.response_summary'));
        $this->console->table($this->getReportTableHeaders(), $this->getCompletedQuestions());

        $this->console->info(Lang::get('qanda.questions.completion_percentage', [
            'percentage' => $this->getCompletedPercentage()
        ]));

        [$completed, $total] = $this->getNoOfCompletedQuestions();
        $this->console->info(Lang::get('qanda.questions.total_completion', [
            'completed' => $completed,
            'total' => $total
        ]));

        $this->console->info(Lang::get('qanda.questions.correct_answer.percentage', [
            'percentage' => $this->getPercentageOfCorrectAnswers()
        ]));

        [$rightAnswerCounts, $wrongAnswerCounts] = $this->getNoOfCompletedRightAnswers();
        $this->console->info(Lang::get('qanda.questions.answers', [
            'rightAnswer' => $rightAnswerCounts,
            'wrongAnswer' => $wrongAnswerCounts
        ]));
    }

    /**
     * @return string[]
     */
    public function getReportTableHeaders(): array
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
    public function getCompletedQuestions()
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
}