<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Initiate payment for a contract.
     */
    public function initiate(Request $request, Contract $contract): RedirectResponse
    {
        try {
            $payment = $this->paymentService->initiatePayment(Auth::user(), $contract);

            return redirect()->route('payments.checkout', $payment)
                ->with('success', 'تم بدء جلسة الدفع بنجاح. يرجى إتمام عملية السداد.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }
    }

    /**
     * Display checkout screen for a payment intent.
     */
    public function checkout(Payment $payment): View
    {
        $user = Auth::user();

        if ((int) $payment->client_id !== (int) $user->id && ! $user->is_admin) {
            abort(403, 'غير مصرح لك بالوصول لصفحة السداد هذه.');
        }

        $payment->load(['contract.project', 'contract.freelancer', 'client']);

        $rate = (float) config('monetization.commission_rate', 0.10);
        $commissionEstimate = round((float) $payment->amount * $rate, 2);
        $freelancerEstimate = round((float) $payment->amount - $commissionEstimate, 2);

        return view('payments.checkout', compact('payment', 'rate', 'commissionEstimate', 'freelancerEstimate'));
    }

    /**
     * Process sandbox callback / verification.
     */
    public function callback(Request $request, Payment $payment): RedirectResponse
    {
        $user = Auth::user();

        if ((int) $payment->client_id !== (int) $user->id && ! $user->is_admin) {
            abort(403, 'غير مصرح لك بمعالجة هذا الدفع.');
        }

        $simulatedStatus = $request->input('simulated_status', 'paid');

        try {
            $updatedPayment = $this->paymentService->processPaymentCallback($payment, $simulatedStatus);

            if ($updatedPayment->isPaid()) {
                return redirect()->route('contracts.show', $updatedPayment->contract)
                    ->with('success', 'تم إتمام وسداد قيمة العقد بنجاح وحساب عمولة المنصة!');
            }

            return redirect()->route('contracts.show', $updatedPayment->contract)
                ->withErrors(['payment' => 'تعذرت عملية السداد أو تم إلغاؤها من بوابه الدفع.']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }
    }

    /**
     * Admin Payments Overview List.
     */
    public function adminIndex(Request $request): View
    {
        $query = Payment::query()
            ->with(['contract.project', 'client', 'commission'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('contract', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $payments = $query->paginate(20)->withQueryString();

        $totalGrossPaid = (float) Payment::where('status', 'paid')->sum('amount');
        $totalCommissionEarned = (float) \App\Models\PlatformCommission::where('status', 'calculated')->sum('commission_amount');

        return view('admin.payments', compact('payments', 'totalGrossPaid', 'totalCommissionEarned'));
    }
}
