<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $defaultIndex = 0;

        $this->info('Welcome to interactive Q And A system');

        // Create your interactive Q And A system here. Be sure to make use of all of Laravels functionalities.

        $chosenOption = $this->choice(
            'What will you like to do?', [
                'Add Questions And Answer', 'View Previous Answers'
            ],
            $defaultIndex
        );
    }
}
