<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Display the form to request resending verification email.
     */
    public function create(): View
    {
        return view('auth.resend-verification');
    }

    /**
     * Send a new email verification notification for guest (by email).
     */
    public function resend(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        // Generic message for security (prevents account enumeration)
        return back()->with('status', 'Se o e-mail informado estiver cadastrado e ainda não verificado, um novo link de confirmação foi enviado!');
    }

    /**
     * Send a new email verification notification for authenticated user.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
