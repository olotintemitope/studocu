<?php


namespace App\Console\Commands\Repositories;


use App\Console\Commands\Contracts\BaseInterface;
use App\Models\Question;

class QuestionRepository implements BaseInterface
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
}