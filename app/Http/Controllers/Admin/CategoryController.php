<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('posts')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->slug($data['name'], $data['slug'] ?? null);

        Category::create($data);

        return back()->with('status', 'تم إنشاء القسم.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category->id);
        $data['slug'] = $this->slug($data['name'], $category->slug);

        $category->update($data);

        return back()->with('status', 'تم تحديث القسم.');
    }

    public function destroy(Category $category)
    {
        if ($category->posts()->exists()) {
            return back()->withErrors(['error' => 'لا يمكن حذف قسم يحتوي مقالات. انقل المقالات أولًا.']);
        }

        $category->delete();

        return back()->with('status', 'تم حذف القسم.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($id)],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('categories', 'slug')->ignore($id)],
            'description' => ['nullable', 'string', 'max:300'],
            'sort_order' => ['nullable', 'integer', 'between:0,255'],
            'is_active' => ['nullable', 'boolean'],
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