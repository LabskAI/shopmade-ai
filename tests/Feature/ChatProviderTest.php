<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Shopmade\Ai\ChatProvider;
use Tests\TestCase;

pest()->extend(TestCase::class);

it('returns null without a key and sends nothing', function () {
    config(['ai.chat.provider' => 'xai', 'ai.providers.xai.key' => null]);
    Http::fake();

    expect(ChatProvider::complete('system', 'user'))->toBeNull();
    Http::assertNothingSent();
});

it('calls the xai endpoint with temperature and returns the reply', function () {
    config(['ai.chat.provider' => 'xai', 'ai.providers.xai.key' => 'test-key', 'ai.models.chat' => null]);
    Http::fake(['api.x.ai/*' => Http::response(['choices' => [['message' => ['content' => '  Hello  ']]]])]);

    expect(ChatProvider::complete('system', 'user', 0.4))->toBe('Hello');

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.x.ai/v1/chat/completions'
        && $request->hasHeader('Authorization', 'Bearer test-key')
        && $request['model'] === 'grok-4.6'
        && $request['temperature'] === 0.4);
});

it('uses the anthropic v1 endpoint and default model without temperature', function () {
    config(['ai.chat.provider' => 'anthropic', 'ai.providers.anthropic.key' => 'test-key', 'ai.models.chat' => null]);
    Http::fake(['api.anthropic.com/*' => Http::response(['choices' => [['message' => ['content' => 'Hi']]]])]);

    expect(ChatProvider::complete('system', 'user', 0.4))->toBe('Hi');

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.anthropic.com/v1/chat/completions'
        && $request['model'] === 'claude-opus-5-5'
        && ! isset($request['temperature']));
});

it('returns null on a failed response', function () {
    config(['ai.chat.provider' => 'xai', 'ai.providers.xai.key' => 'test-key']);
    Http::fake(['*' => Http::response([], 500)]);

    expect(ChatProvider::complete('system', 'user'))->toBeNull();
});

it('shows status without keys', function () {
    config(['ai.chat.provider' => 'xai', 'ai.providers.xai.key' => 'secret-value']);

    $this->artisan('ai:status')
        ->expectsOutputToContain('grok-4.6')
        ->doesntExpectOutputToContain('secret-value')
        ->assertSuccessful();
});
