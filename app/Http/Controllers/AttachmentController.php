<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Project;
use App\Services\AttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttachmentController extends Controller
{
    public function __construct(
        protected AttachmentService $attachmentService
    ) {}

    public function storeProjectAttachment(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ], [
            'file.required' => 'يرجى اختيار ملف المرفق.',
            'file.max' => 'حجم الملف يتجاوز الحد المسموح (20 ميجابايت).',
        ]);

        try {
            $this->attachmentService->upload(Auth::user(), $project, $request->file('file'));

            return back()->with('success', 'تم رفع المرفق بنجاح.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        return $this->storeProjectAttachment($request, $project);
    }

    public function download(mixed $projectOrAttachment, ?Attachment $attachment = null)
    {
        $target = $attachment ?? ($projectOrAttachment instanceof Attachment ? $projectOrAttachment : Attachment::findOrFail($projectOrAttachment));

        $this->authorize('download', $target);

        return $this->attachmentService->download($target);
    }

    public function replace(Request $request, mixed $projectOrAttachment, ?Attachment $attachment = null): RedirectResponse
    {
        $target = $attachment ?? ($projectOrAttachment instanceof Attachment ? $projectOrAttachment : Attachment::findOrFail($projectOrAttachment));

        $this->authorize('update', $target);

        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ], [
            'file.required' => 'يرجى اختيار الملف الجديد.',
        ]);

        try {
            $this->attachmentService->replace($target, $request->file('file'));

            return back()->with('success', 'تم استبدال المرفق بنجاح.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }
    }

    public function destroy(mixed $projectOrAttachment, ?Attachment $attachment = null): RedirectResponse
    {
        $target = $attachment ?? ($projectOrAttachment instanceof Attachment ? $projectOrAttachment : Attachment::findOrFail($projectOrAttachment));

        $this->authorize('delete', $target);

        $this->attachmentService->delete($target);

        return back()->with('success', 'تم حذف المرفق بنجاح.');
    }
}
