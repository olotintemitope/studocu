<?php


namespace App\Console\Commands\Traits;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Whoops\Exception\ErrorException;

trait QAndATraits
{
    /**
     * @return array
     */
    protected function getInitializedVar(): array
    {
        $answer = null;
        $qAndA = [];
        return [$answer, $qAndA];
    }

    /**
     * @param string $answer
     * @param $qAndA
     * @param string $question
     * @return array
     */
    protected function getAnsweredQuestions(string $answer, $qAndA, string $question): array
    {
        $answers = explode(',', trim($answer));
        $qAndA[$question][] = $answers;

        return [$answers, $qAndA];
    }

    /**
     * @param $answers
     * @param $qAndA
     * @param array|null $question
     * @return mixed
     */
    protected function getChosenAnswers($answers, $qAndA, string $question)
    {
        $chosenAnswer = $this->inputReader->chooseAnAnswer($answers);
        $qAndA[$question][] = $chosenAnswer;
        return $qAndA;
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
     * @param string $question
     * @return array
     */
    protected function getChosenQuestion(string $question): array
    {
        $chosenQuestion = $this->question->model()::OfWhereQuestion($question)->first();

        $answerProvided = $this->inputReader->chooseAnyQuestion(
            $chosenQuestion->question,
            $chosenQuestion->options->pluck('option')->toArray()
        );
        return [$chosenQuestion, $answerProvided];
    }

    /**
     * @param string $question
     * @return bool
     */
    protected function readyToExit(string $question): bool
    {
        return $question === Lang::get('qanda.exit') ||
            $question === strtolower(Lang::get('qanda.exit'));
    }

    /**
     * @param Collection $questions
     * @return array
     */
    protected function getQuestionsWithExitOption(Collection $questions): array
    {
        $questions->toArray();
        $questions[] = Lang::get('qanda.exit');

        return $questions->toArray();
    }

    /**
     * @param string $question
     * @return bool
     */
    protected function userWillNotExit(string $question): bool
    {
        return !empty($question) && !$this->readyToExit($question);
    }
}