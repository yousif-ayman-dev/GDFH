<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        event(new Registered($user));

        // Send real-time notification to all platform admins
        try {
            $admins = User::where('is_admin', true)->get();
            $notificationService = app(\App\Services\NotificationService::class);
            $accountTypeText = $user->isClient() ? 'صاحب عمل' : 'مستقل';

            foreach ($admins as $admin) {
                $notificationService->sendNotification(
                    $admin,
                    'مستخدم جديد انضم للمنصة 🚀',
                    'سجل المستخدم الجديد "' . $user->name . '" (' . $user->email . ') كـ ' . $accountTypeText . '.',
                    route('admin.users', ['search' => $user->email])
                );
            }
        } catch (\Throwable $e) {}

        Auth::login($user);

        $request->session()->regenerate();

        return redirect(route('dashboard', absolute: false));
    }
}
