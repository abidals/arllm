<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Services\AI\AiManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AiController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('admin.ai.index', [
            'providers' => AiProvider::orderByDesc('is_default')->orderBy('label')->get(),
            'actions' => app(AiManager::class)->actions(),
            'aiEnabled' => \App\Models\Setting::get('ai_enabled', '0') === '1',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $provider = AiProvider::create($data + ['created_by' => auth()->id()]);
        $provider->api_key = trim((string) $request->input('api_key'));
        $provider->save();
        $this->ensureDefault(request: $request);

        return back()->with('status', 'تم إضافة المزوّد والمفتاح مشفّر AES-256.');
    }

    public function update(Request $request, AiProvider $provider)
    {
        $data = $this->validated($request, $provider);

        // المفتاح لا يتغير ما لم يُرسَل جديد
        $newKey = trim((string) $request->input('api_key'));

        if ($newKey !== '') {
            $provider->api_key = $newKey;
        }

        $provider->fill($data);
        $provider->save();

        if ($provider->is_default) {
            AiProvider::where('id', '!=', $provider->id)->update(['is_default' => false]);
        }

        return back()->with('status', 'تم تحديث المزوّد. المفتاح مشفّر ومقنّع.');
    }

    public function destroy(AiProvider $provider)
    {
        $provider->delete();

        return back()->with('status', 'تم حذف المزوّد.');
    }

    public function makeDefault(AiProvider $provider)
    {
        AiProvider::where('id', '!=', $provider->id)->update(['is_default' => false]);
        $provider->update(['is_default' => true, 'is_active' => true]);

        return back()->with('status', 'تم تعيين «'.$provider->label.'» كمزوّد افتراضي.');
    }

    /**
     * توليد النص للوحدة (المقال/الصفحة). المفتاح لا يغادر الخادم أبدًا.
     */
    public function generate(Request $request)
    {
        if (\App\Models\Setting::get('ai_enabled', '0') !== '1') {
            return response()->json(['error' => 'المساعد معطّل — فعّله من الإعدادات.'], 403);
        }

        $validated = $request->validate([
            'action' => ['required', Rule::in(array_keys((new AiManager)->actions()))],
            'text' => ['required', 'string', 'max:12000'],
            'context' => ['nullable', Rule::in(['post', 'page'])],
        ]);

        try {
            $text = app(AiManager::class)->complete($validated['action'], $validated['text']);
        } catch (\Throwable $e) {
            Log::warning('AI generate failed', [
                'provider' => AiProvider::where('is_default', true)->value('label') ?? '-',
                'error' => mb_substr($e->getMessage(), 0, 200),
            ]);

            return response()->json(['error' => 'تعذر توليد النص من المزوّد. تحقق من المفتاح والاتصال.'], 502);
        }

        return response()->json(['text' => $text]);
    }

    /* ---------------- التحقق ---------------- */

    private function validated(Request $request, ?AiProvider $provider = null): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'driver' => ['required', Rule::in(['openai-compatible', 'anthropic'])],
            'base_url' => ['required', 'url:https', 'max:255'],
            'model' => ['required', 'string', 'max:100'],
            'max_tokens' => ['nullable', 'integer', 'between:128,8192'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'api_key' => [$provider ? 'nullable' : 'required', 'string', 'min:8', 'max:300'],
        ]);

        unset($data['api_key']);

        return $data;
    }

    private function ensureDefault(Request $request): void
    {
        $defaultId = AiProvider::where('is_default', true)->orderByDesc('updated_at')->value('id')
            ?? AiProvider::orderByDesc('updated_at')->value('id');

        if ($defaultId) {
            AiProvider::whereKey($defaultId)->update(['is_default' => true, 'is_active' => true]);
            AiProvider::where('id', '!=', $defaultId)->update(['is_default' => false]);
        }
    }
}