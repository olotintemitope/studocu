<?php


namespace App\Console\Commands\Repositories;


use App\Console\Commands\Contracts\BaseInterface;
use App\Models\Answer;
use App\Models\Question;

class AnswerRepository implements BaseInterface
{
    /**
     * @var Question
     */
    private $model;

    public function __construct(Answer $question)
    {
        $this->model = $question;
    }

    public function insert(array $data): void
    {
        $this->model::insert($data);
    }

    /**
     * @param array $data
     * @return array
     */
    public function prepareData(array $data): array
    {
        $chosenAnswers = [];
        $allChosenAnswers = [];
        foreach ($data as $question => $answers) {
            $chosenAnswers['answer'] = $answers[1];
            $chosenAnswers['question_id'] = Question::OfWhereQuestion($question)->first()->id;
            $chosenAnswers['created_at'] = now();

            $allChosenAnswers[] = $chosenAnswers;
        }
        return $allChosenAnswers;
    }
}