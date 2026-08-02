<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    public function storeProjectComment(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'body.required' => 'يرجى كتابة التعليق أولاً.',
            'body.max' => 'التعليق يتجاوز الحد المسموح (2000 حرف).',
        ]);

        $this->commentService->createComment(Auth::user(), $project, $validated['body']);

        return back()->with('success', 'تم إضافة التعليق بنجاح.');
    }

    public function storeReply(Request $request, Comment $comment): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'body.required' => 'يرجى كتابة الرد أولاً.',
            'body.max' => 'الرد يتجاوز الحد المسموح (2000 حرف).',
        ]);

        $this->commentService->reply(Auth::user(), $comment, $validated['body']);

        return back()->with('success', 'تم إضافة الرد بنجاح.');
    }

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'body.required' => 'محتوى التعليق مطلوب.',
        ]);

        $this->commentService->updateComment($comment, $validated['body']);

        return back()->with('success', 'تم تعديل التعليق بنجاح.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $this->commentService->deleteComment($comment);

        return back()->with('success', 'تم حذف التعليق بنجاح.');
    }
}
