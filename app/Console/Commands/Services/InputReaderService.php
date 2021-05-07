<?php


namespace App\Console\Commands\Services;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;

class InputReaderService
{
    /**
     * @var Command
     */
    private $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Select the options
     *
     * @return string
     */
    public function chooseOption(): string
    {
        $defaultIndex = 0;

        return $this->command
            ->choice(
                Lang::get('qanda.question'), [
                Lang::get('qanda.question.opt_1'),
                Lang::get('qanda.question.opt_2'),
                Lang::get('qanda.exit'),
            ],
                $defaultIndex
            );
    }

    public function chooseAnAnswer(array $answers): string
    {
        return $this->command
            ->choice(
                Lang::get('qanda.question.answers.answer.ask'), $answers
            );
    }

    public function askQuestion(): string
    {
        return $this->command
            ->ask(Lang::get('qanda.question.ask'));
    }

    /**
     * @return mixed
     */
    public function provideAnAnswer(): ?string
    {
        return $this->command
            ->ask(Lang::get('qanda.question.answers.ask'));
    }

    public function chooseAnyQuestion(string $lang, array $questions): string
    {
        return $this->command->choice(
            $lang,
            $questions
        );
    }
}