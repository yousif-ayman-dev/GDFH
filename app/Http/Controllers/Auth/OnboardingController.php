<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Display the onboarding view.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasCompletedOnboarding()) {
            return redirect()->route('dashboard');
        }

        return view('auth.onboarding');
    }

    /**
     * Handle the onboarding submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasCompletedOnboarding()) {
            return redirect()->route('dashboard');
        }

        if ($request->has('username')) {
            $request->merge([
                'username' => Str::lower(trim((string) $request->username)),
            ]);
        }

        $validated = $request->validate([
            'account_type' => ['required', 'string', 'in:client,freelancer'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique(User::class, 'username')->ignore($user->id),
            ],
        ], [
            'account_type.required' => 'يرجى اختيار نوع الحساب (مستقل أو عميل).',
            'account_type.in' => 'نوع الحساب المحدد غير صالح.',
            'username.required' => 'يرجى إدخال اسم المستخدم الخاص بك.',
            'username.min' => 'يجب أن يتكون اسم المستخدم من 3 أحرف على الأقل.',
            'username.max' => 'يجب ألا يتجاوز اسم المستخدم 30 حرفاً.',
            'username.regex' => 'اسم المستخدم يمكن أن يحتوي فقط على أحرف إنجليزية، أرقام، وشرطة سفلية (_).',
            'username.unique' => 'اسم المستخدم هذا مستخدم بالفعل، يرجى اختيار اسم آخر.',
        ]);

        $user->update([
            'account_type' => $validated['account_type'],
            'username' => $validated['username'],
            'onboarded_at' => now(),
        ]);

        $request->session()->regenerateToken();

        return redirect()
            ->route('dashboard')
            ->with('success', 'تم إكمال إعداد حسابك بنجاح! مرحباً بك في GDFH.');
    }
}
