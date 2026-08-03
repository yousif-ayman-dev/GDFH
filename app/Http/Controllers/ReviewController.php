<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewService $reviewService
    ) {}

    public function index(Project $project): View
    {
        $reviews = $this->reviewService->getProjectReviews($project);

        return view('projects.show', compact('project', 'reviews'));
    }

    public function create(Project $project): View
    {
        return view('projects.show', compact('project'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'reviewee_id' => ['nullable', 'exists:users,id'],
        ]);

        try {
            $this->reviewService->submitReview(
                Auth::user(),
                $project,
                (int) $request->input('rating'),
                $request->input('comment'),
                $request->filled('reviewee_id') ? (int) $request->input('reviewee_id') : null
            );

            return redirect()->route('projects.reviews.index', $project)
                ->with('success', 'شكراً لك! تم تسليم التقييم بنجاح.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors([
                'review' => $e->getMessage(),
                'reviewee_id' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Project $project, Review $review): RedirectResponse
    {
        $user = Auth::user();

        if ((int) $review->reviewer_id !== (int) $user->id && (int) $project->owner_id !== (int) $user->id) {
            abort(403, 'غير مصرح لك بحذف هذا التقييم.');
        }

        $review->delete();

        return redirect()->route('projects.reviews.index', $project)
            ->with('success', 'تم حذف التقييم.');
    }
}
