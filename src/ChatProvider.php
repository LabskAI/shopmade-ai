<?php

namespace Shopmade\Ai;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * The OpenAI-compatible chat endpoint that packages call. Which vendor answers is `ai.chat.provider`
 * (xai, openrouter, anthropic): its key and base URL come from `ai.providers.<name>`, the model from
 * `ai.models.chat` or the vendor's default.
 */
class ChatProvider
{
    /** @var array<string, string> */
    private const DefaultModels = [
        'xai' => 'grok-4.6',
        'openrouter' => 'openrouter/auto',
        'anthropic' => 'claude-opus-5-5',
    ];

    /**
     * Vendors whose current models reject sampling parameters such as temperature.
     *
     * @var list<string>
     */
    private const NoSampling = ['anthropic'];

    public static function name(): string
    {
        $name = trim((string) config('ai.chat.provider', 'xai'));

        return $name !== '' ? $name : 'xai';
    }

    public static function key(): string
    {
        return (string) config('ai.providers.'.self::name().'.key', '');
    }

    public static function baseUrl(): string
    {
        return rtrim((string) config('ai.providers.'.self::name().'.base_url', ''), '/');
    }

    public static function model(): string
    {
        $configured = (string) config('ai.models.chat', '');

        return $configured !== '' ? $configured : (self::DefaultModels[self::name()] ?? 'grok-4.6');
    }

    public static function isConfigured(): bool
    {
        return self::key() !== '' && self::baseUrl() !== '';
    }

    /**
     * A request ready for POST /chat/completions, or null when the provider has no key or URL.
     * OpenRouter asks for a referer and a title so the app shows up in its dashboard.
     */
    public static function request(int $timeoutSeconds = 25): ?PendingRequest
    {
        if (! self::isConfigured()) {
            return null;
        }

        $request = Http::baseUrl(self::baseUrl())
            ->withToken(self::key())
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout($timeoutSeconds);

        if (self::name() === 'openrouter') {
            $request = $request->withHeaders([
                'HTTP-Referer' => (string) config('app.url'),
                'X-Title' => (string) config('app.name'),
            ]);
        }

        return $request;
    }

    /**
     * One system + user turn. The reply text, or null when the provider is not configured, fails or answers empty.
     * Temperature is dropped for vendors that reject it.
     */
    public static function complete(string $system, string $user, ?float $temperature = null, int $timeoutSeconds = 25): ?string
    {
        $request = self::request($timeoutSeconds);

        if ($request === null) {
            return null;
        }

        $body = [
            'model' => self::model(),
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ];

        if ($temperature !== null && ! in_array(self::name(), self::NoSampling, true)) {
            $body['temperature'] = $temperature;
        }

        try {
            $response = $request->post('/chat/completions', $body);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        return is_string($content) && trim($content) !== '' ? trim($content) : null;
    }
}
