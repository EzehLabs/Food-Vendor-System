<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\SignInLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, SignInLinkService $signInLinks): RedirectResponse
    {
        $signInLinks->sendTo($request->validated('email'));

        return redirect()->route('login')->with('status', 'login-link-sent');
    }

    /**
     * Authenticate a user with a valid, single-use sign-in link.
     */
    public function consume(
        Request $request,
        int $user,
        string $token,
        SignInLinkService $signInLinks
    ): RedirectResponse {
        $account = User::find($user);

        if (! $account || ! $signInLinks->consume($account, $token)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'This sign-in link is invalid or expired. Please request a new one.']);
        }

        if (! $account->hasVerifiedEmail()) {
            $account->markEmailAsVerified();
        }

        Auth::login($account);
        $request->session()->regenerate();

        return match ($account->role) {
            'vendor' => redirect('/vendor/dashboard'),
            'admin' => redirect('/admin/dashboard'),
            default => redirect('/customer/home'),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
