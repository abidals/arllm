<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">المستخدمون</h2>
            <a href="{{ route('admin.users.create') }}" class="bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                + مستخدم جديد
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600">الاسم</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600 hidden sm:table-cell">البريد</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600">الدور</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600 hidden md:table-cell">مقالات</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($users as $u)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <div class="font-semibold text-gray-900">{{ $u->approvedName() }}</div>
                                <div class="text-xs text-gray-500 sm:hidden">{{ $u->email }}</div>
                            </td>
                            <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $u->email }}</td>
                            <td class="px-5 py-3">
                                @php $roleColors = ['admin' => 'bg-red-100 text-red-800', 'editor' => 'bg-blue-100 text-blue-800', 'author' => 'bg-green-100 text-green-800']; @endphp
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $roleColors[$u->roles->first()?->name] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ['admin' => 'مدير', 'editor' => 'محرر', 'author' => 'كاتب'][$u->roles->first()?->name] ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $u->posts->count() }}</td>
                            <td class="px-5 py-3 text-left whitespace-nowrap">
                                <a href="{{ route('admin.users.edit', $u) }}" class="text-blue-700 hover:underline font-medium">تعديل</a>
                                @if ($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline ms-2"
                                          onsubmit="return confirm('حذف المستخدم نهائيًا؟ ستُنقل مقالاته إليك.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-700 hover:underline font-medium">حذف</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>