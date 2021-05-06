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

    public function insert(array $data): array
    {
        $this->model::insert($data);
    }
}