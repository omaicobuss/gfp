<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     */
    public function __invoke(Request $request, $id, $hash): RedirectResponse
    {
        $user = User::find($id);

        if (! $user) {
            return redirect()->route('login')->with('error', 'Usuário não encontrado.');
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->with('error', 'Link de confirmação inválido ou expirado.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'Seu e-mail já foi verificado anteriormente. Faça login para continuar.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('login')->with('status', 'E-mail confirmado com sucesso! Você já pode fazer login na sua conta.');
    }
}
