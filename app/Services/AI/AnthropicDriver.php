<?php

namespace App\Services\AI;

use App\Models\AiProvider;
use Illuminate\Support\Facades\Http;

/** مزوّد Anthropic (Claude) */
class AnthropicDriver
{
    public function __construct(private AiProvider $provider) {}

    public function complete(string $system, string $user): string
    {
        $base = rtrim($this->provider->base_url, '/');

        $response = Http::withHeaders([
                'x-api-key' => $this->provider->decryptKey(),
                'anthropic-version' => '2023-06-01',
            ])
            ->timeout(90)
            ->connectTimeout(10)
            ->acceptJson()
            ->post("{$base}/v1/messages", [
                'model' => $this->provider->model,
                'max_tokens' => $this->provider->max_tokens,
                'system' => $system,
                'messages' => [
                    ['role' => 'user', 'content' => $user],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('فشل طلب المزوّد ('.$response->status().').');
        }

        $blocks = $response->json('content');
        $text = '';

        foreach ((array) $blocks as $block) {
            $text .= $block['text'] ?? '';
        }

        return trim($text);
    }
}