<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttachmentController extends Controller
{
    public function store(StoreAttachmentRequest $request, Project $project): RedirectResponse
    {
        $this->ensureProjectOwner($project);

        $data = $request->validated();
        $file = $data['file'];

        $path = $file->store('attachments', 'local');

        $attachment = Attachment::create([
            'uploaded_by' => Auth::id(),
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $file->hashName(),
            'disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'extension' => $file->extension(),
            'size' => $file->getSize(),
            'visibility' => $data['visibility'] ?? 'private',
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Attachment uploaded successfully.');
    }

    public function destroy(Project $project, Attachment $attachment): RedirectResponse
    {
        $this->ensureProjectOwner($project);
        $this->ensureAttachmentBelongsToProject($project, $attachment);

        if ($attachment->path && Storage::disk($attachment->disk)->exists($attachment->path)) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        $attachment->delete();

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Attachment deleted successfully.');
    }

    private function ensureProjectOwner(Project $project): void
    {
        abort_unless($project->owner_id === Auth::id(), 403);
    }

    private function ensureAttachmentBelongsToProject(Project $project, Attachment $attachment): void
    {
        abort_unless(
            $attachment->attachable_type === Project::class && $attachment->attachable_id === $project->id,
            404
        );
    }
}
