<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        if ($user = $request->user()) {
            $validated = $request->validate([
                'body' => 'required|string|min:2|max:2000',
            ]);

            Comment::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'name' => $user->approvedName(),
                'email' => $user->email,
                'body' => $validated['body'],
                'status' => 'pending',
            ]);
        } else {
            $validated = $request->validate([
                'name' => 'required|string|max:80',
                'email' => ['required', 'email:rfc', 'max:120'],
                'body' => 'required|string|min:2|max:2000',
            ]);

            Comment::create([
                'post_id' => $post->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'body' => strip_tags($validated['body']),
                'status' => 'pending',
                'ip_address' => $request->ip(),
            ]);
        }

        return back()->with('comment_status', 'تم استلام تعليقك وسيظهر بعد مراجعة المحرر.');
    }
}