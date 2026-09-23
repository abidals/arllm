<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">تعديل: {{ Str::limit($post->title, 40) }}</h2>
            <a href="{{ route('admin.posts.index') }}" class="text-sm text-gray-500 hover:text-gray-900">← كل المقالات</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('admin.posts._form', [
            'post' => $post,
            'categories' => $categories,
            'tags' => $tags,
            'action' => route('admin.posts.update', $post),
        ])
    </div>
</x-app-layout>