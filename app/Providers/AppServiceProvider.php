<?php

namespace App\Providers;

use App\Console\Commands\Contracts\AnswerInterface;
use App\Console\Commands\Contracts\OptionInterface;
use App\Console\Commands\Contracts\QuestionInterface;
use App\Console\Commands\Contracts\ResponseInterface;
use App\Console\Commands\Repositories\AnswerRepository;
use App\Console\Commands\Repositories\OptionRepository;
use App\Console\Commands\Repositories\QuestionRepository;
use App\Console\Commands\Repositories\ResponseRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(QuestionInterface::class, QuestionRepository::class);
        $this->app->bind(OptionInterface::class, OptionRepository::class);
        $this->app->bind(AnswerInterface::class, AnswerRepository::class);
        $this->app->bind(ResponseInterface::class, ResponseRepository::class);
    }
}
