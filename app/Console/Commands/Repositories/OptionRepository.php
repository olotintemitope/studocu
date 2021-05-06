<?php


namespace App\Console\Commands\Repositories;


use App\Console\Commands\Contracts\BaseInterface;
use App\Models\Option;
use App\Models\Question;

class OptionRepository implements BaseInterface
{
    /**
     * @var Question
     */
    private $model;

    public function __construct(Option $question)
    {
        $this->model = $question;
    }

    public function insert(array $data): void
    {
        $this->model::insert($data);
    }

    public function prepareData(array $data): array
    {
        $allOptions = [];
        foreach ($data as $question => $answers) {
            $options = [];
            $qOptions = $answers[0];
            foreach ($qOptions as $option) {
                $options['option'] = $option;
                $options['question_id'] = Question::OfWhereQuestion($question)->first()->id;
                $options['created_at'] = now();

                $allOptions[] = $options;
            }
        }
        return $allOptions;
    }
}