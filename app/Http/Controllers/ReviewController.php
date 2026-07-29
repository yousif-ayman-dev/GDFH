<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Project;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Project $project): View
    {
        $this->ensureProjectOwner($project);

        $reviews = $project->reviews()->latest()->paginate(10);

        return view('reviews.index', compact('project', 'reviews'));
    }

    public function create(Project $project): View
    {
        $this->ensureProjectOwner($project);

        return view('reviews.create', compact('project'));
    }

    public function store(StoreReviewRequest $request, Project $project): RedirectResponse
    {
        $this->ensureProjectOwner($project);

        $data = $request->validated();

        Review::create([
            'project_id' => $project->id,
            'reviewer_id' => Auth::id(),
            'reviewee_id' => $data['reviewee_id'],
            'rating' => $data['rating'],
            'communication_rating' => $data['communication_rating'] ?? null,
            'quality_rating' => $data['quality_rating'] ?? null,
            'professionalism_rating' => $data['professionalism_rating'] ?? null,
            'deadline_rating' => $data['deadline_rating'] ?? null,
            'comment' => $data['comment'] ?? null,
            'status' => $data['status'] ?? 'published',
        ]);

        return redirect()
            ->route('projects.reviews.index', $project)
            ->with('success', 'Review created successfully.');
    }

    public function edit(Project $project, Review $review): View
    {
        $this->ensureProjectOwner($project);
        $this->ensureReviewBelongsToProject($project, $review);

        return view('reviews.edit', compact('project', 'review'));
    }

    public function update(UpdateReviewRequest $request, Project $project, Review $review): RedirectResponse
    {
        $this->ensureProjectOwner($project);
        $this->ensureReviewBelongsToProject($project, $review);

        $review->update($request->validated());

        return redirect()
            ->route('projects.reviews.index', $project)
            ->with('success', 'Review updated successfully.');
    }

    public function destroy(Project $project, Review $review): RedirectResponse
    {
        $this->ensureProjectOwner($project);
        $this->ensureReviewBelongsToProject($project, $review);

        $review->delete();

        return redirect()
            ->route('projects.reviews.index', $project)
            ->with('success', 'Review deleted successfully.');
    }

    private function ensureProjectOwner(Project $project): void
    {
        abort_unless($project->owner_id === Auth::id(), 403);
    }

    private function ensureReviewBelongsToProject(Project $project, Review $review): void
    {
        abort_unless($review->project_id === $project->id, 404);
    }
}
