<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Proposal;
use App\Services\ProposalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    public function __construct(
        protected ProposalService $proposalService
    ) {}

    public function store(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'bid_amount' => ['required', 'numeric', 'min:1'],
            'delivery_days' => ['required', 'integer', 'min:1', 'max:365'],
            'cover_letter' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        try {
            $detector = new \App\Services\Security\OffPlatformDetectorService();
            $inspection = $detector->inspectAndFilter($request->input('cover_letter'));

            $this->proposalService->submitProposal(
                Auth::user(),
                $project,
                (float) $request->input('bid_amount'),
                (int) $request->input('delivery_days'),
                $inspection['clean_text']
            );

            return back()->with('success', 'تم تقديم عرضك على المشروع بنجاح!');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['proposal' => $e->getMessage()]);
        }
    }

    public function accept(Proposal $proposal): RedirectResponse
    {
        try {
            $contract = $this->proposalService->acceptProposal(Auth::user(), $proposal);

            return redirect()->route('contracts.show', $contract)
                ->with('success', 'تم قبول العرض وإصدار عقد الاتفاقية بنجاح!');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['proposal' => $e->getMessage()]);
        }
    }

    public function reject(Proposal $proposal): RedirectResponse
    {
        try {
            $this->proposalService->rejectProposal(Auth::user(), $proposal);

            return back()->with('success', 'تم رفض العرض.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['proposal' => $e->getMessage()]);
        }
    }

    public function withdraw(Proposal $proposal): RedirectResponse
    {
        try {
            $this->proposalService->withdrawProposal(Auth::user(), $proposal);

            return back()->with('success', 'تم سحب عرضك بنجاح.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['proposal' => $e->getMessage()]);
        }
    }
}
