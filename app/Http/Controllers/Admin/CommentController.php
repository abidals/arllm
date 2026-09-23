<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $filter = $request->query('filter', 'pending');

        $query = Comment::with(['post:id,title,slug', 'user:id,name,display_name'])
            ->latest();

        $comments = match ($filter) {
            'approved' => $query->approved(),
            'spam' => $query->where('status', 'spam'),
            'all' => $query,
            default => $query->pending(),
        };

        $comments = $comments->paginate(20)->withQueryString();

        return view('admin.comments.index', [
            'comments' => $comments,
            'filter' => $filter,
            'pendingCount' => Comment::pending()->count(),
        ]);
    }

    public function approve(Comment $comment)
    {
        $this->authorize('moderate', $comment);

        $comment->status = 'approved';
        $comment->save();

        return back()->with('status', 'تمت الموافقة على التعليق.');
    }

    public function spam(Comment $comment)
    {
        $this->authorize('moderate', $comment);

        $comment->status = 'spam';
        $comment->save();

        return back()->with('status', 'تم تصنيف التعليق كبريد مزعج.');
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('status', 'تم حذف التعليق.');
    }
}