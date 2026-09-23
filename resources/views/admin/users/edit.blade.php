<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">تعديل المستخدم: {{ $editingUser->approvedName() }}</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('admin.users._form', ['editingUser' => $editingUser, 'roles' => $roles])
    </div>
</x-app-layout>