<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect user to Google OAuth page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if (! $user) {
                $user = User::create([
                    'name' => $googleUser->getName() ?? 'مستخدم Google',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar_path' => null,
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                    'account_type' => 'freelancer',
                ]);
            } else {
                if (empty($user->google_id)) {
                    $user->google_id = $googleUser->getId();
                }
                if (! $user->hasVerifiedEmail()) {
                    $user->email_verified_at = now();
                }
                $user->save();
            }

            Auth::login($user);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'تم تسجيل الدخول بواسطة حساب Google بنجاح!');
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'حدث خطأ أثناء الاتصال بحساب Google. يرجى المحاولة لاحقاً.']);
        }
    }
}
