<?php

namespace App\Services\AI;

use App\Models\AiProvider;
use Illuminate\Support\Facades\Http;

/**
 * مزوّد متوافق مع واجهة OpenAI:
 * OpenAI / DeepSeek / Groq / Together / Mistral / Ollama المحلي / أي مزوّد خاص.
 */
class OpenAiCompatibleDriver
{
    public function __construct(private AiProvider $provider) {}

    public function complete(string $system, string $user): string
    {
        $base = rtrim($this->provider->base_url, '/');

        $response = Http::withToken($this->provider->decryptKey())
            ->timeout(90)
            ->connectTimeout(10)
            ->acceptJson()
            ->post("{$base}/chat/completions", [
                'model' => $this->provider->model,
                'max_tokens' => $this->provider->max_tokens,
                'temperature' => 0.4,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('فشل طلب المزوّد ('.$response->status().').');
        }

        $text = $response->json('choices.0.message.content');

        return $this->clean($text ?? '');
    }

    private function clean(string $text): string
    {
        $text = trim($text);

        // إزالة أسوار ```markdown إن غلّفها النموذج
        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```[a-zA-Z]*\n|\n```$/', '', $text);
        }

        return trim((string) $text);
    }
}