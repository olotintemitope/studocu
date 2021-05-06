<?php

namespace App\Console\Commands;

use App\Console\Commands\Repositories\AnswerRepository;
use App\Console\Commands\Repositories\OptionRepository;
use App\Console\Commands\Repositories\QuestionRepository;
use App\Console\Commands\Services\InputReaderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Whoops\Exception\ErrorException;

class QAndA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qanda:interactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs an interactive command line based Q And A system.';
    /**
     * @var InputReaderService
     */
    private $inputReader;
    /**
     * @var QuestionRepository
     */
    private $question;
    /**
     * @var OptionRepository
     */
    private $option;
    /**
     * @var AnswerRepository
     */
    private $answer;

    /**
     * Create a new command instance.
     * @param InputReaderService $inputReader
     * @param QuestionRepository $questionRepository
     * @param OptionRepository $optionRepository
     * @param AnswerRepository $answerRepository
     */
    public function __construct (
        InputReaderService $inputReader,
        QuestionRepository $questionRepository,
        OptionRepository $optionRepository,
        AnswerRepository $answerRepository
    )
    {
        parent::__construct();

        $this->inputReader = new $inputReader($this);
        $this->question = $questionRepository;
        $this->option = $optionRepository;
        $this->answer = $answerRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Welcome to interactive Q And A system');
        // Create your interactive Q And A system here. Be sure to make use of all of Laravels functionalities.
        $chosenOption = $this->inputReader->chooseOption();

        if ($chosenOption === Lang::get('qanda.question.opt_1')) {
            $answer = null;
            $chosenAnswer = null;
            $qAndA = [];

            do {
                $question = $this->inputReader->askQuestion();
                if ($question === Lang::get('qanda.exit')) {
                    break;
                }
                if (!empty($question) && $question !== Lang::get('qanda.exit')) {
                    $qAndA[$question] = [];
                    $answer = $this->inputReader->provideAnswers();
                }
                if (!empty($answer)) {
                    $answers = explode(', ', trim($answer));
                    $qAndA[$question][] = $answers;

                    $chosenAnswer = $this->inputReader->chooseAnAnswer($answers);
                    $qAndA[$question][] = $chosenAnswer;
                }
            } while(true);

            try {
                $this->InsertQAndA($qAndA);
                $this->info(Lang::get('qanda.insert_success_msg'));
            } catch (\RuntimeException $exception) {
                $this->error($exception->getMessage());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }

        if ($chosenOption === Lang::get('qanda.question.opt_2')) {
        }
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
}
