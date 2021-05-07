<?php

namespace Tests\Unit;

use App\Console\Commands\Contracts\AnswerInterface;
use App\Console\Commands\Contracts\OptionInterface;
use App\Console\Commands\Contracts\QuestionInterface;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionTest extends TestCase
{
    private $question;
    /**
     * @var mixed
     */
    private $option;
    /**
     * @var mixed
     */
    private $answer;
    private $model;

    use TestStubTraits;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');

        $this->question = $this->app->make(QuestionInterface::class);
        $this->option = $this->app->make(OptionInterface::class);
        $this->answer = $this->app->make(AnswerInterface::class);

        //Create a new record
        $this->model = $this->question->create($this->getQuestionStubs());
        $this->option->insert($this->getQuestionOptionsStubs($this->model->id));
        $this->answer->insert($this->getQuestionAnswerStubs($this->model->id));
    }

    public const CHOOSE_NEW_YORK = 0;

    /**
     * @return void
     */
    public function testUserCanAddQuestions(): void
    {
        $this->artisan('qanda:interactive')
            ->expectsQuestion(Lang::get('qanda.question'), Lang::get('qanda.question.opt_1'))
            ->expectsQuestion(Lang::get('qanda.question.ask'), $this->model->question)
            ->expectsQuestion(Lang::get('qanda.question.answers.ask'), implode(',', $this->model->options->pluck('option')->toArray()))
            ->expectsQuestion(Lang::get('qanda.question.answers.answer.ask'), $this->model->options->pluck('option')->first())

            ->expectsQuestion(Lang::get('qanda.question.ask'), Lang::get('qanda.exit'))
            ->expectsOutput(Lang::get('qanda.bye'))
        ;
    }

    public function testUserCanViewPreviousAnswers(): void
    {
        $this->artisan('qanda:interactive')
            ->expectsQuestion(Lang::get('qanda.question'), Lang::get('qanda.question.opt_2'))
            ->expectsQuestion(Lang::get('qanda.question_to_practice'), $this->model->question)
            ->expectsQuestion($this->model->question, $this->model->options->pluck('option')->first())
            ->expectsOutput(Lang::get('qanda.insert_success_msg'))
            ->expectsQuestion(Lang::get('qanda.question_to_practice'), Lang::get('qanda.exit'))
            ->assertExitCode(0)
        ;
    }

    public function testThatUserCanExitFromOptions() : void
    {
        $this->artisan('qanda:interactive')
            ->expectsQuestion(Lang::get('qanda.question'), Lang::get('qanda.exit'))
            ->expectsOutput(Lang::get('qanda.bye'))
            ;
    }

    public function testThatUserCanExitWhenViewingPreviousQuestions() : void
    {
        $this->artisan('qanda:interactive')
            ->expectsQuestion(Lang::get('qanda.question'), Lang::get('qanda.question.opt_2'))
            ->expectsQuestion(Lang::get('qanda.question_to_practice'), Lang::get('qanda.exit'))
            ->expectsOutput(Lang::get('qanda.response_summary'))
            ;
    }

    public function testThatUserCanResetPreviouslyAnsweredQuestions() : void
    {
        $this->artisan('qanda:reset')
            ->expectsOutput(Lang::get('qanda.reset_msg'));
    }
}
