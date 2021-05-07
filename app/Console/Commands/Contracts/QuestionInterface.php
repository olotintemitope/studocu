<?php


namespace App\Console\Commands\Contracts;


use Illuminate\Support\Collection;

interface QuestionInterface extends BaseInterface
{
    public function getUnansweredQuestions(): Collection;
}