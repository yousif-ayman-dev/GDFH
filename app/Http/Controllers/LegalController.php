<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function terms(): View
    {
        return view('legal.terms');
    }

    public function privacy(): View
    {
        return view('legal.privacy');
    }

    public function paymentPolicy(): View
    {
        return view('legal.payment-policy');
    }

    public function refundPolicy(): View
    {
        return view('legal.refund-policy');
    }

    public function marketplaceRules(): View
    {
        return view('legal.marketplace-rules');
    }
}
