<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use App\Models\User;
use App\Services\AuditService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->home(Auth::user()));
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $key = 'login:'.Str::lower($request->input('email')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $remember = $request->boolean('remember');
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            if ($user->status !== 'active') {
                Auth::logout();
                throw ValidationException::withMessages(['email' => 'Your account is inactive. Please contact an administrator.']);
            }
            RateLimiter::clear($key);
            $request->session()->regenerate();
            $user->forceFill(['last_login_at' => now()])->save();
            LoginHistory::create(['user_id' => $user->id, 'email' => $user->email, 'ip' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 500), 'successful' => true]);
            AuditService::log('auth.login', $user);

            return redirect()->intended($this->home($user));
        }

        RateLimiter::hit($key, 60);
        LoginHistory::create(['email' => $request->input('email'), 'ip' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 500), 'successful' => false]);

        throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
    }

    public function logout(Request $request)
    {
        AuditService::log('auth.logout', $request->user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function sendReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        // Also route through our EmailService/log so it appears in Email Logs when possible.
        return back()->with('success', 'If that email exists, a password reset link has been sent.');
    }

    public function showReset(string $token, Request $request)
    {
        return view('auth.reset', ['token' => $token, 'email' => $request->get('email')]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );
        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Password reset successful. Please sign in.');
        }
        throw ValidationException::withMessages(['email' => __($status)]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);
        $user = $request->user();
        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'Current password is incorrect.']);
        }
        $user->update(['password' => Hash::make($request->password)]);
        AuditService::log('auth.password_changed', $user);
        if ($user->email) {
            app(EmailService::class)->send('auth.password_changed', $user->email, ['user_name' => $user->name]);
        }
        return back()->with('success', 'Password changed successfully.');
    }

    protected function home(User $user): string
    {
        return match ($user->primaryPortal()) {
            'advertiser' => route('portal.advertiser'),
            'driver' => route('portal.driver'),
            'owner' => route('portal.owner'),
            'technician' => route('portal.technician'),
            default => route('dashboard'),
        };
    }
}
