<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('admin.users.index', [
            'users' => User::with('roles')->orderBy('name')->get(),
        ]);
    }

    public function create(): \Illuminate\View\View
    {
        return view('admin.users.create', ['roles' => Role::where('name', '!=', 'admin')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $user = User::create([
            'name' => $data['name'],
            'display_name' => $data['display_name'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'bio' => $data['bio'] ?? null,
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('admin.users.index')->with('status', 'تم إنشاء المستخدم.');
    }

    public function edit(User $user): \Illuminate\View\View
    {
        return view('admin.users.edit', [
            'editingUser' => $user,
            'roles' => Role::where('name', '!=', 'admin')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user, withPassword: $request->filled('password'));

        $user->name = $data['name'];
        $user->display_name = $data['display_name'] ?? null;
        $user->email = $data['email'];
        $user->bio = $data['bio'] ?? null;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        if (!empty($data['role']) && $user->id !== auth()->id()) {
            $user->syncRoles([$data['role']]);
        }

        return redirect()->route('admin.users.index')->with('status', 'تم تحديث المستخدم.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'لا يمكنك حذف حسابك.']);
        }

        if ($user->isAdmin() && User::role('admin')->count() <= 1) {
            return back()->withErrors(['error' => 'يجب بقاء مدير واحد على الأقل.']);
        }

        $user->posts()->update(['user_id' => auth()->id()]);
        $user->delete();

        return back()->with('status', 'تم حذف المستخدم.');
    }

    private function validated(Request $request, ?User $user = null, bool $withPassword = false): array
    {
        $emailRule = Rule::unique('users', 'email')->ignore($user?->id);

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'display_name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:150', $emailRule],
            'bio' => ['nullable', 'string', 'max:1000'],
            'role' => ['required', Rule::exists('roles', 'name')],
        ];

        if ($withPassword || $user === null) {
            $rules['password'] = ['required', 'string', 'min:10', 'max:72'];
        }

        return $request->validate($rules);
    }
}