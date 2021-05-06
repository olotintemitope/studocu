<?php


namespace App\Console\Commands\Services;

use App\Console\Commands\Repositories\QuestionRepository;
use App\Console\Commands\Repositories\ResponseRepository;
use App\Models\Response;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var QuestionInterface
     */
    private $question;

    public function __construct(ResponseRepository $response, QuestionRepository $question)
    {
        $this->response = $response;
        $this->question = $question;
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
            $this->question->getAll()->count(),
            $this->response->getAll()->count(),
        ];
    }

    public function getPercentageOfCorrectAnswers(): int
    {
        $responses = $this->response->getAll();

        $answers = $this->getResponseAnswers($responses);

        return ($answers['right_answer'] / $responses->count()) * 100;
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
}