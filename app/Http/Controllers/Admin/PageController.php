<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('admin.pages.index', ['pages' => Page::orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function store(Request $request)
    {
        $page = Page::create($this->data($request));
        $page->renderContent();
        $page->save();

        return back()->with('status', 'تم إنشاء الصفحة.');
    }

    public function update(Request $request, Page $page)
    {
        $page->fill($this->data($request, $page));
        $page->renderContent();
        $page->save();

        return back()->with('status', 'تم تحديث الصفحة.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return back()->with('status', 'تم حذف الصفحة.');
    }

    private function data(Request $request, ?Page $page = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220'],
            'subtitle' => ['nullable', 'string', 'max:200'],
            'content_md' => ['required', 'string', 'max:100000'],
            'is_published' => ['nullable', 'boolean'],
            'show_in_nav' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'between:0,255'],
        ]);

        $slug = trim((string) ($validated['slug'] ?? ''));
        $validated['slug'] = $slug !== ''
            ? $slug
            : Page::uniqueSlug($validated['title'], $page?->id);

        return $validated;
    }
}