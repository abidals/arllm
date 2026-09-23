<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('admin.categories.index', [
            'parents' => Category::parents()->with('children')->withCount('posts')->get(),
            'allCategories' => Category::withCount('posts')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->slug($data['name'], $data['slug'] ?? null);

        Category::create($data);
        Category::flushCache();

        return back()->with('status', 'تم إنشاء القسم.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category);
        $data['slug'] = $this->slug($data['name'], $category->slug);

        $category->update($data);
        Category::flushCache();

        return back()->with('status', 'تم تحديث القسم.');
    }

    public function destroy(Category $category)
    {
        if ($category->posts()->exists() || $category->children()->exists()) {
            return back()->withErrors(['error' => 'لا يمكن حذف قسم يحتوي مقالات أو أقسامًا فرعية.']);
        }

        $category->delete();
        Category::flushCache();

        return back()->with('status', 'تم حذف القسم.');
    }

    /**
     * ترتيب الأقسام بالسحب والإفلات: [{id, parent_id, sort_order}]
     */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'items.*.parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'items.*.sort_order' => ['required', 'integer', 'between:0,255'],
        ]);

        $items = collect($data['items']);
        $ids = $items->pluck('id')->unique();
        $parentIds = $items->pluck('parent_id')->filter()->unique();

        // الأب يجب أن يكون قسمًا رئيسيًا فقط (أقصى عمق: مستويان)
        if (Category::whereKey($parentIds)->whereNotNull('parent_id')->exists()) {
            return response()->json(['error' => 'لا يمكن التداخل أكثر من مستويين.'], 422);
        }

        foreach ($items as $item) {
            if ((int) $item['parent_id'] === (int) $item['id']) {
                return response()->json(['error' => 'ترتيب غير صالح.'], 422);
            }
        }

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                Category::where('id', $item['id'])->update([
                    'parent_id' => $item['parent_id'],
                    'sort_order' => $item['sort_order'],
                ]);
            }
        });

        Category::flushCache();

        return response()->json(['ok' => true]);
    }

    /* ---------------- التحقق ---------------- */

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($id)],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('categories', 'slug')->ignore($id)],
            'description' => ['nullable', 'string', 'max:300'],
            'sort_order' => ['nullable', 'integer', 'between:0,255'],
            'is_active' => ['nullable', 'boolean'],
            'parent_id' => [
                'nullable',
                Rule::in(Category::parents()->pluck('id')->all()),
                Rule::notIn([$id]),
            ],
        ]);
    }

    private function slug(string $name, ?string $custom): string
    {
        $ascii = Str::slug($custom ?: $name);
        if ($ascii !== '' && $ascii !== '-') {
            return $ascii;
        }

        $arabic = trim(trim(preg_replace('/[^\p{Arabic}\p{N}\s-]/u', '', $name)));
        $arabic = trim(preg_replace('/\s+/u', '-', $arabic), '-');

        return $arabic !== '' ? $arabic : 'cat-'.Str::random(6);
    }
}