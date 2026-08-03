<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\ContractService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function __construct(
        protected ContractService $contractService
    ) {}

    public function index(): View
    {
        $contracts = $this->contractService->getUserContracts(Auth::user());

        return view('contracts.index', compact('contracts'));
    }

    public function show(Contract $contract): View
    {
        $user = Auth::user();

        if ((int) $contract->client_id !== (int) $user->id && (int) $contract->freelancer_id !== (int) $user->id) {
            abort(403, 'غير مصرح لك بالوصول لهذا العقد.');
        }

        $contract->load(['client', 'freelancer', 'project', 'proposal']);

        return view('contracts.show', compact('contract'));
    }

    public function complete(Contract $contract): RedirectResponse
    {
        try {
            $this->contractService->completeContract(Auth::user(), $contract);

            return back()->with('success', 'تم إتمام العقد وتسليم المشروع بنجاح!');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['contract' => $e->getMessage()]);
        }
    }
}
