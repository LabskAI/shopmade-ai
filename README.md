# shopmade-ai

One AI chat client for [Shopmade core](https://github.com/LabskAI/shopmade-core) packages. It talks to any OpenAI-compatible chat endpoint: xAI, OpenRouter or Anthropic. MIT licensed.

## Install

```bash
composer require labskai/shopmade-ai
php artisan ai:status   # provider, model, and which keys are set (never the keys)
```

## Configure

| Env | What |
| --- | --- |
| `AI_CHAT_PROVIDER` | `xai` (default), `openrouter` or `anthropic` |
| `AI_CHAT_MODEL` | Optional. Defaults: `grok-4.6`, `openrouter/auto`, `claude-opus-5-5` |
| `XAI_API_KEY` / `OPENROUTER_API_KEY` / `ANTHROPIC_API_KEY` | The provider's key. `AI_API_KEY` is a fallback for xAI and Anthropic |
| `AI_XAI_BASE_URL`, `AI_OPENROUTER_BASE_URL`, `AI_ANTHROPIC_BASE_URL` | Optional base URL overrides |

Publish the config with `php artisan vendor:publish --tag=shopmade-ai-config` to change more.

## Use

```php
use Shopmade\Ai\ChatProvider;

$reply = ChatProvider::complete($systemPrompt, $userMessage, temperature: 0.4); // null when not configured or on failure
```

`ChatProvider::request()` returns a ready `PendingRequest` for anything else on `/chat/completions`. Temperature is dropped for Anthropic, whose current models reject it.

## Tests

```bash
cd ../shopmade-core
composer config repositories.ai '{"type":"path","url":"../shopmade-ai"}'
composer require "labskai/shopmade-ai:*@dev"
vendor/bin/pest vendor/labskai/shopmade-ai/tests
```
