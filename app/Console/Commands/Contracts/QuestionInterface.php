<?php


namespace App\Console\Commands\Contracts;


use App\Models\Question;
use Illuminate\Support\Collection;

interface QuestionInterface extends BaseInterface
{
    public function getUnansweredQuestions(): Collection;

    public function create(array $data) : Question;
}