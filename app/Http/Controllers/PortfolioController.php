<?php

namespace App\Http\Controllers;

use App\Models\PortfolioItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        if (! $user->isFreelancer() && ! $user->isAdmin()) {
            abort(403, 'إدارة معرض الأعمال متاحة فقط للمستقلين.');
        }

        $portfolioItems = $user->portfolioItems()->paginate(12);

        return view('portfolio.index', compact('portfolioItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isFreelancer() && ! $user->isAdmin()) {
            abort(403, 'إدارة معرض الأعمال متاحة فقط للمستقلين.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'project_url' => ['nullable', 'url', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'skills' => ['nullable', 'string'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('portfolios', 'public');
        }

        $skillsArray = [];
        if (! empty($validated['skills'])) {
            $skillsArray = array_filter(array_map('trim', explode(',', $validated['skills'])));
        }

        PortfolioItem::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'project_url' => $validated['project_url'] ?? null,
            'category' => $validated['category'] ?? null,
            'skills' => $skillsArray,
        ]);

        return back()->with('success', 'تم إضافة عمل جديد إلى معرض أعمالك بنجاح!');
    }

    public function destroy(PortfolioItem $portfolioItem): RedirectResponse
    {
        if ((int) $portfolioItem->user_id !== (int) Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'غير مصرح لك بحذف هذا العمل.');
        }

        if ($portfolioItem->image_path) {
            Storage::disk('public')->delete($portfolioItem->image_path);
        }

        $portfolioItem->delete();

        return back()->with('success', 'تم حذف العمل من معرض أعمالك بنجاح.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->isFreelancer() && ! $user->isAdmin()) {
            abort(403, 'غير مصرح لك بإجراء هذه العملية.');
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'skills' => ['nullable', 'string'],
            'cv_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // 10MB PDF
        ]);

        $profile = $user->freelancerProfile()->firstOrCreate(['user_id' => $user->id]);

        if ($request->hasFile('cv_file')) {
            if ($profile->cv_path && Storage::disk('public')->exists($profile->cv_path)) {
                Storage::disk('public')->delete($profile->cv_path);
            }
            $profile->cv_path = $request->file('cv_file')->store('cvs', 'public');
            $profile->save();
        }

        $skillsArray = [];
        if (! empty($validated['skills'])) {
            $skillsArray = array_filter(array_map('trim', explode(',', $validated['skills'])));
        }

        $profile->update([
            'title' => $validated['title'] ?? $profile->title,
            'bio' => $validated['bio'] ?? $profile->bio,
            'hourly_rate' => $validated['hourly_rate'] ?? $profile->hourly_rate,
            'location' => $validated['location'] ?? $profile->location,
            'skills' => ! empty($skillsArray) ? $skillsArray : $profile->skills,
        ]);

        if (array_key_exists('bio', $validated)) {
            $user->update(['bio' => $validated['bio']]);
        }

        return back()->with('success', 'تم تحديث البيانات والسيرة الذاتية بنجاح!');
    }
}
