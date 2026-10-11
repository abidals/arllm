<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media as MediaAsset;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $assets = MediaAsset::orderByDesc('id')->get();
        $assets->each(fn ($asset) => $asset->usage = $this->findUsage($asset));

        return view('admin.media.index', ['assets' => $assets]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array', 'max:12'],
            'files.*' => ['file', 'max:4096', 'mimes:jpg,jpeg,png,webp,gif,svg,pdf,md,txt'],
        ]);

        $count = 0;

        foreach ($request->file('files') as $file) {
            $ext = strtolower($file->getClientOriginalExtension());

            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'pdf', 'md', 'txt'])) {
                continue;
            }

            $name = bin2hex(random_bytes(12)).'.'.$ext;

            if ($ext === 'svg') {
                $content = $file->getContent();
                $content = $this->sanitizeSvg($content);
                Storage::disk('public')->put('library/'.$name, $content);
            } else {
                $file->storeAs('library', $name, 'public');
            }

            $mime = strtolower($file->getMimeType() ?? '');

            MediaAsset::create([
                'user_id' => auth()->id(),
                'path' => 'library/'.$name,
                'original_name' => mb_substr(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 0, 100),
                'mime_type' => $mime ?: 'application/octet-stream',
                'size' => Storage::disk('public')->size('library/'.$name),
            ]);

            $count++;
        }

        return back()->with('status', "تمت إضافة $count ملفًا إلى المكتبة.");
    }

    public function destroy(MediaAsset $media)
    {
        $usage = $this->findUsage($media);

        if ($usage->isNotEmpty()) {
            $names = $usage->pluck('label')->implode('، ');

            return back()->withErrors(['error' => 'الملف مرتبط بـ: '.$names.' — استبدل الاستخدام أولًا، أو استخدم «حذف قسري» الذي يفرّغ كل الاستخدامات ثم يحذف الملف.']);
        }

        Storage::disk('public')->delete($media->path);
        $media->delete();

        return back()->with('status', 'تم حذف الملف نهائيًا.');
    }

    /**
     * حذف قسري: يفرّغ كل الاستخدامات (مقالات، صور شخصية، إعدادات) ثم يحذف الملف.
     */
    public function forceDestroy(MediaAsset $media)
    {
        $path = $media->path;

        Post::withTrashed()->where('featured_image', $path)->update(['featured_image' => null]);
        User::where('avatar', $path)->update(['avatar' => null]);

        foreach (['site_logo', 'site_favicon', 'heading_font_path', 'body_font_path'] as $key) {
            if ((string) Setting::get($key) === $path) {
                Setting::set($key, null);
            }
        }

        Storage::disk('public')->delete($path);
        $media->delete();

        return back()->with('status', 'تم الحذف القسري مع تفريغ جميع مراجع الاستخدام.');
    }

    public function rename(Request $request, MediaAsset $media)
    {
        $data = $request->validate([
            'original_name' => ['required', 'string', 'max:100'],
        ]);

        $media->update(['original_name' => $data['original_name']]);

        return back()->with('status', 'تم إعادة التسمية.');
    }

    public function replace(Request $request, MediaAsset $media)
    {
        $request->validate([
            'replacement' => ['required', 'file', 'max:4096'],
        ]);

        $file = $request->file('replacement');
        $newExt = strtolower($file->getClientOriginalExtension());
        $currentExt = pathinfo($media->path, PATHINFO_EXTENSION);
        $isImage = in_array($currentExt, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);

        if ($isImage && !in_array($newExt, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
            return back()->withErrors(['error' => 'الاستبدال يتطلب صورة بنفس الفئة (jpg/png/webp/gif/svg).']);
        }

        if ($newExt === 'svg') {
            Storage::disk('public')->put($media->path, $this->sanitizeSvg($file->getContent()));
        } else {
            Storage::disk('public')->put($media->path, $file->getContent());
        }

        $media->update([
            'size' => Storage::disk('public')->size($media->path),
        ]);

        return back()->with('status', 'تم استبدال محتوى الملف — الجلب يبقى على نفس الرابط.');
    }

    /* ---------------- مساعدة الاستخدام ---------------- */

    private function findUsage(MediaAsset $media)
    {
        // مسار الملف قد يكون بدون بادئة 'library/…' الكاملة الحالية — فحص شامل عبر paths الخرى
        $path = $media->path;
        $relPath = preg_replace('#^library/#', '', $path);
        $usages = collect();

        Post::withTrashed()
            ->where('featured_image', 'like', "%{$path}%")
            ->get(['id', 'title', 'slug'])
            ->each(fn ($p) => $usages->push(['type' => 'مقال', 'label' => 'مقال: '.$p->title]));

        $user = User::where('avatar', 'like', "%{$path}%")->first();
        if ($user) {
            $usages->push((object) ['type' => 'صورة شخصية', 'label' => 'مستخدم: '.$user->approvedName()]);
        }

        $settingsKeys = [
            'site_logo' => 'لوجو الموقع',
            'site_favicon' => 'أيقونة المتصفح',
            'heading_font_path' => 'خط العناوين',
            'body_font_path' => 'خط النصوص',
        ];

        foreach ($settingsKeys as $key => $title) {
            if (str_contains((string) Setting::get($key), $path)) {
                $usages->push((object) ['type' => 'هوية الموقع', 'label' => 'إعدادات الموقع: '.$title]);
            }
        }

        return $usages;
    }

    private function sanitizeSvg(string $svg): string
    {
        $svg = preg_replace('/<script[\s\S]*?<\/script>/i', '', $svg);
        $svg = preg_replace('/\son[a-zA-Z]+\s*=\s*"[^"]*"/i', '', $svg);
        $svg = preg_replace('/\son[a-zA-Z]+\s*=\s*\'[^\']*\'/i', '', $svg);
        $svg = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\\2/i', '$1="#"', $svg);

        return $svg;
    }
}