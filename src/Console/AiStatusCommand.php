<?php

namespace Shopmade\Ai\Console;

use Illuminate\Console\Command;
use Shopmade\Ai\ChatProvider;

class AiStatusCommand extends Command
{
    protected $signature = 'ai:status';

    protected $description = 'Show the chat provider, model and which AI keys are set (never the keys)';

    public function handle(): int
    {
        $this->table(['Setting', 'Value'], [
            ['Chat provider', ChatProvider::name()],
            ['Chat model', ChatProvider::model()],
            ['Base URL', ChatProvider::baseUrl()],
            ['Ready', ChatProvider::isConfigured() ? 'yes' : 'no'],
        ]);

        $this->table(['Key', 'Env', 'Set'], [
            ['xAI', 'XAI_API_KEY or AI_API_KEY', filled(config('ai.providers.xai.key')) ? 'yes' : 'no'],
            ['Anthropic', 'ANTHROPIC_API_KEY or AI_API_KEY', filled(config('ai.providers.anthropic.key')) ? 'yes' : 'no'],
            ['OpenRouter', 'OPENROUTER_API_KEY', filled(config('ai.providers.openrouter.key')) ? 'yes' : 'no'],
        ]);

        return self::SUCCESS;
    }
}
