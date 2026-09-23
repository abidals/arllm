{{-- نموذج إنشاء/تعديل مقال. الاستخدام: $post (اختياري)، $categories، $tags (اختياري)، $action --}}
@php
    $action = $action ?? (isset($post) ? route('admin.posts.update', $post) : route('admin.posts.store'));
    $method = isset($post) ? 'PUT' : 'POST';
    $pending = isset($post) && $post->status === 'review';
    $scheduledStatus = isset($post) && $post->status === 'scheduled' ? 'scheduled' : '';
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="grid lg:grid-cols-[1fr_320px] gap-6 items-start">
    @csrf
    @if (isset($post))
        @method('PUT')
    @endif

    {{-- العمود الرئيسي --}}
    <div class="space-y-5">
        <div class="bg-white rounded-xl shadow p-6 space-y-5">
            @if (session('imported_markdown') && !isset($post))
                <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-900">
                    تم استيراد الملف «<strong>{{ session('imported_title') }}</strong>» — المحتوى أدناه.
                </div>
            @endif

            <div>
                <label for="title" class="block text-sm font-bold mb-1.5">عنوان المقال <span class="text-red-600">*</span></label>
                <input id="title" name="title" type="text" required maxlength="200" value="{{ old('title', $post->title ?? session('imported_title')) }}"
                       class="w-full text-lg border-gray-300 rounded-lg focus:border-gray-900 focus:ring-gray-900">
                @error('title') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                @error('slug') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="subtitle" class="block text-sm font-bold mb-1.5">العنوان الفرعي</label>
                <input id="subtitle" name="subtitle" type="text" maxlength="200" value="{{ old('subtitle', $post->subtitle ?? '') }}"
                       class="w-full border-gray-300 rounded-lg focus:border-gray-900 focus:ring-gray-900">
            </div>

            <div>
                <label for="excerpt" class="block text-sm font-bold mb-1.5">المقتطف (ملخص الـ SEO)</label>
                <textarea id="excerpt" name="excerpt" rows="2" maxlength="500"
                          class="w-full border-gray-300 rounded-lg font-naskh">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">يُنشأ تلقائيًا من بداية النص عند الترك فارغًا.</p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="content_md" class="text-sm font-bold">المحتوى (Markdown) <span class="text-red-600">*</span></label>

                    {{-- استيراد ملف .md --}}
                    <details class="text-sm">
                        <summary class="cursor-pointer font-semibold text-blue-700 hover:underline">استيراد ملف .md</summary>
                        <form method="POST" action="{{ route('admin.posts.import-md') }}" enctype="multipart/form-data" class="mt-2 flex items-center gap-2">
                            @csrf
                            <input type="file" name="file" accept=".md,.markdown,.txt" required
                                   class="block w-56 text-xs border border-gray-300 rounded file:me-3 file:py-1 file:px-3 file:rounded file:border-0 file:bg-gray-900 file:text-white file:text-xs">
                            <button class="bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded">تحويل</button>
                        </form>
                    </details>
                </div>

                <textarea id="content_md" name="content_md" rows="20" required
                          class="w-full border-gray-300 rounded-lg font-mono text-sm leading-relaxed focus:border-gray-900 focus:ring-gray-900"
                          placeholder="اكتب محتوى المقال بصيغة Markdown…&#10;&#10;## عنوان فرعي&#10;فقرة عادية. **عريض** و*مائل* و [رابط](https://example.com)">{{ old('content_md', session('imported_markdown', $post->content_md ?? '')) }}</textarea>
                @error('content_md') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-500 mt-1">
                    يدعم التنسيق القياسي: العناوين، <code>#</code> القوائم، الجداول، الاقتباس، والروابط. يُنظف HTML الخام تلقائيًا لأسباب أمنية.
                </p>
            </div>

            <div>
                <label for="tags" class="block text-sm font-bold mb-1.5">وسوم (مفصولة بفواصل)</label>
                <input id="tags" name="tags" type="text" maxlength="300" value="{{ old('tags', $tags ?? '') }}"
                       class="w-full border-gray-300 rounded-lg focus:border-gray-900 focus:ring-gray-900"
                       placeholder="اقتصاد، سياسة، تضخم">
            </div>
        </div>
    </div>

    {{-- العمود الجانبي: النشر --}}
    <div class="space-y-5 lg:sticky lg:top-24">
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="font-bold border-b border-gray-200 pb-3">النشر</h3>

            @if (session('status'))
                <div class="rounded bg-green-50 border border-green-200 text-green-800 px-3 py-2 text-sm">{{ session('status') }}</div>
            @endif

            <div>
                <label for="category_id" class="block text-sm font-bold mb-1.5">القسم <span class="text-red-600">*</span></label>
                <select id="category_id" name="category_id" required
                        class="w-full border-gray-300 rounded-lg text-sm bg-white">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $post->category_id ?? $category->id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="publish_at" class="block text-sm font-bold mb-1.5">وقت النشر</label>
                <input id="publish_at" name="publish_at" type="datetime-local"
                       value="{{ old('publish_at', isset($post) ? optional($post->published_at)?->format('Y-m-d\TH:i') : '') }}"
                       class="w-full border-gray-300 rounded-lg text-sm">
                <p class="text-xs text-gray-500 mt-1">اتركه فارغًا للنشر الفوري.</p>
            </div>

            <div class="space-y-3 pt-2 border-t border-gray-200">
                @can('publish')
                    <label class="flex items-start gap-2 text-sm">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', isset($post) && $post->is_featured))
                               class="mt-1 rounded border-gray-300 text-red-700 focus:ring-red-700">
                        <span>
                            <strong>مميز (واجهة الرئيسية)</strong>
                            <span class="block text-[11px] text-gray-500 leading-relaxed mt-0.5">
                                يُبرز المقال في «مميزة اليوم». الأحدث المنشور يصبح <strong>قصة الغلاف</strong> تلقائيًا.
                                حاليًا: {{ $featuredCount ?? 0 }} مقال مميز.
                            </span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 text-sm">
                        <input type="checkbox" name="is_sponsored" value="1" @checked(old('is_sponsored', isset($post) && $post->is_sponsored))
                               class="mt-1 rounded border-gray-300 text-red-700 focus:ring-red-700">
                        <span>
                            <strong>محتوى إعلاني</strong>
                            <span class="block text-[11px] text-gray-500 leading-relaxed mt-0.5">
                                يظهر في صندوق وسط الرئيسية علائمه الملصق «{{ \App\Models\Setting::get('sponsored_label', 'محتوى إعلاني') }}» — لا يلتحق بالفهرسة الرئيسية.
                            </span>
                        </span>
                    </label>
                @endcan
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="font-bold border-b border-gray-200 pb-3">الحالة</h3>
            @if (isset($post))
                <p class="text-sm text-gray-600">
                    الحالة الحالية: <strong>{{ ['draft' => 'مسودة', 'review' => 'بانتظار المراجعة', 'scheduled' => 'مجدول', 'published' => 'منشور', 'archived' => 'أرشيف'][$post->status] ?? $post->status }}</strong>
                </p>
            @endif

            <div class="grid gap-2">
                <button type="submit" name="status_action" value="draft"
                        class="w-full border border-gray-300 rounded-lg py-2.5 text-sm font-semibold hover:bg-gray-50">
                    حفظ كمسودة
                </button>

                <button type="submit" name="status_action" value="review"
                        class="w-full bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg py-2.5 text-sm font-semibold">
                    @can('publish') إرسال للمراجعة @else طلب النشر (مراجعة) @endcan
                </button>

                @can('publish')
                    <button type="submit" name="status_action" value="schedule"
                            class="w-full bg-blue-700 hover:bg-blue-800 text-white rounded-lg py-2.5 text-sm font-semibold">
                        جدولة النشر
                    </button>

                    <button type="submit" name="status_action" value="publish"
                            class="w-full bg-red-700 hover:bg-red-800 text-white rounded-lg py-2.5 text-sm font-semibold">
                        @if (isset($post) && $post->status === 'published') تحديث المنشور @else نشر الآن @endif
                    </button>
                @endcan
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="font-bold border-b border-gray-200 pb-3">الصورة الرئيسية</h3>
            @if (isset($post) && $post->featured_image)
                <img src="{{ Storage::url($post->featured_image) }}" alt="" class="rounded-lg w-full">
            @endif
            <input type="file" name="featured_image" accept=".jpg,.jpeg,.png,.webp,.gif"
                   class="block w-full text-xs border border-gray-300 rounded file:me-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-gray-900 file:text-white">
            <p class="text-xs text-gray-500">jpg/png/webp/gif — حتى 4MB.</p>
            @error('featured_image') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
        </div>

        @if (isset($post))
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-bold border-b border-gray-200 pb-3 mb-3">معاينة</h3>
                <a href="{{ route('site.post', $post->slug) }}" target="_blank" rel="noopener" class="text-blue-700 hover:underline text-sm font-semibold">
                    فتح المقال على الموقع ↗
                </a>
            </div>
        @endif

        @include('admin.ai._panel', ['context' => 'post'])
    </div>
</form>