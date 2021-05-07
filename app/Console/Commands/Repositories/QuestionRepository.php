<?php


namespace App\Console\Commands\Repositories;

use App\Console\Commands\Contracts\QuestionInterface;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Support\Collection;

class QuestionRepository implements QuestionInterface
{
    /**
     * @var Question
     */
    private $model;

    public function __construct(Question $question)
    {
        $this->model = $question;
    }

    public function insert(array $data): void
    {
        $this->model::insert($data);
    }

    public function prepareData(array $data): array
    {
        $questions = [];
        $allQuestions = [];
        foreach ($data as $question => $answers) {
            $questions['question'] = $question;
            $questions['created_at'] = now();

            $allQuestions[] = $questions;
        }

        return $allQuestions;
    }

    public function getAll(): Collection
    {
        return $this->model::all();
    }

    public function model(): Question
    {
        return $this->model;
    }

    /**
     * @return Collection
     */
    public function getUnansweredQuestions(): Collection
    {
        $answeredQuestions = Response::all()->pluck('question_id')->toArray();

        return $this->getAll()
            ->filter(static function ($question) use ($answeredQuestions) {
                return !in_array($question->id, $answeredQuestions, true);
            })
            ->pluck('question');
    }
}