<?php

namespace Shopmade\Ai;

use Illuminate\Support\ServiceProvider;
use Shopmade\Ai\Console\AiStatusCommand;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ai.php', 'ai');
    }

    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/ai.php' => config_path('ai.php')], 'shopmade-ai-config');

        if ($this->app->runningInConsole()) {
            $this->commands([AiStatusCommand::class]);
        }
    }
}
