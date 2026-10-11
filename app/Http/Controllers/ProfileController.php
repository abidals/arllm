<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->except(['avatar']));

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
                \App\Models\Media::where('path', $user->avatar)->delete();
            }
            $file = $request->file('avatar');
            $ext = strtolower($file->getClientOriginalExtension());

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $name = bin2hex(random_bytes(16)).'.'.$ext;
                $file->storeAs('avatars', $name, 'public');
                $user->avatar = 'avatars/'.$name;

                \App\Models\Media::create([
                    'user_id' => $user->id,
                    'path' => $user->avatar,
                    'original_name' => mb_substr($file->getClientOriginalName(), 0, 100),
                    'mime_type' => strtolower($file->getMimeType() ?? ''),
                    'size' => \Illuminate\Support\Facades\Storage::disk('public')->size($user->avatar),
                ]);
            }
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'تم تحديث ملفك الشخصي.');
    }

    /**
     * Remove the user's avatar.
     */
    public function removeAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            \App\Models\Media::where('path', $user->avatar)->delete();
            $user->avatar = null;
            $user->save();
        }

        return Redirect::route('profile.edit')->with('status', 'تمت إزالة الصورة الشخصية.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
