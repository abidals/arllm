<?php

namespace App\Services\AI;

use App\Models\AiProvider;

class AiManager
{
    public const ACTIONS = [
        'improve' => [
            'system' => 'أنت محرر صحفي عربي محترف في موقع إخباري. حسّن وضوح النص وسلاسه وجودة التعبير مع الحفاظ التام على المعنى والحقائق. لا تخترع معلومات جديدة. أجب بالنص النهائي بصيغة Markdown فقط، بلا عناوين إضافية أو تعليقات.',
            'label' => 'تحسين الصياغة',
        ],
        'excerpt' => [
            'system' => 'أنت محرر عربي. لخّص النص التالي في جملة أو جملتين مقوّيتين (أقل من 160 حرفًا) تصلح مقتطفًا إخباريًا. أجب بالنص فقط.',
            'label' => 'توليد المقتطف',
        ],
        'titles' => [
            'system' => 'أنت رئيس تحرير. اقرأ النص واقترح 3 عناوين عربية إخبارية مركزة (كل عنوان في سطر يبدأ بشرطة -). أجب بالعناوين فقط.',
            'label' => 'اقتراح عناوين',
        ],
        'structure' => [
            'system' => 'أنت مصمم محتوى عربي. أعد تنظيم النص بعدة عناوين فرعية ## وقوائم وجداول Markdown حيث يلزم، دون تغيير المعنى أو إضافة معلومات. أجب بالنص المنسق فقط.',
            'label' => 'إعادة هيكلة (عناصر)',
        ],
    ];

    public function complete(string $action, string $text): string
    {
        $action = $this->actions()[$action] ?? null;

        if (!$action) {
            abort(422, 'إجراء غير معروف.');
        }

        $provider = AiProvider::where('is_default', true)->where('is_active', true)->first()
            ?? AiProvider::where('is_active', true)->first();

        if (!$provider) {
            abort(503, 'لا يوجد مزوّد AI مفعّل — أضف مزوّدًا أولًا.');
        }

        $driver = match ($provider->driver) {
            'anthropic' => new AnthropicDriver($provider),
            default => new OpenAiCompatibleDriver($provider),
        };

        return $driver->complete(
            $action['system'],
            mb_substr($text, 0, 12000)
        );
    }

    public function actions(): array
    {
        return self::ACTIONS;
    }
}