<?php

namespace App\Console\Commands;

use App\Console\Commands\Contracts\ResponseInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;

class Reset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qanda:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset already answered questions';
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * Create a new command instance.
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct();

        $this->response = $response;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->response->reset();

        $this->info(Lang::get('qanda.reset_msg'));
    }
}
