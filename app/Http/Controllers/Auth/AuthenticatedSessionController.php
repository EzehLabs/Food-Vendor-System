<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationService;
use App\Http\Requests\Auth\LoginRequest;
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
    public function store(LoginRequest $request, EmailVerificationService $emailVerification): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (! $request->user()->hasVerifiedEmail()) {
            $emailVerification->send($request->user());

            return redirect()->route('verification.notice')
                ->with('status', 'verification-link-sending');
        }

        if(auth()->user()->role == 'vendor'){
            return redirect('/vendor/dashboard');
            // return redirect()->intended(route('/vendor/dashboard', absolute: false));
        }elseif(auth()->user()->role == 'admin'){ 
            return redirect('/admin/dashboard');
        }else {
            return redirect('/customer/home');
            // return redirect()->intended(route('/customer/home', absolute: false));
        }

        // return redirect()->intended(route('dashboard', absolute: false));
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
