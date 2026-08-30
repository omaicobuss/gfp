<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users for the administrator.
     */
    public function index(Request $request): View
    {
        $query = User::query()->orderByDesc('created_at');

        // Search by name or email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role or status
        if ($status = $request->input('status')) {
            match ($status) {
                'blocked' => $query->where('is_blocked', true),
                'verified' => $query->whereNotNull('email_verified_at')->where('is_blocked', false),
                'unverified' => $query->whereNull('email_verified_at'),
                'admin' => $query->where('role', 'admin'),
                default => null,
            };
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Toggle the blocked status of a user.
     */
    public function toggleBlock(User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        // Impedir auto-bloqueio (Edge Case)
        if ($currentUser->id === $user->id) {
            return redirect()->route('admin.users.index')->with(
                'error',
                'Você não pode bloquear a sua própria conta de administrador.'
            );
        }

        // Se estiver bloqueando um admin, verificar se é o último admin ativo (FR-012)
        if (! $user->is_blocked && $user->isAdmin()) {
            $activeAdminCount = User::where('role', 'admin')
                ->where('is_blocked', false)
                ->where('id', '!=', $user->id)
                ->count();

            if ($activeAdminCount === 0) {
                return redirect()->route('admin.users.index')->with(
                    'error',
                    'Não é possível bloquear este usuário porque ele é o único administrador ativo no sistema.'
                );
            }
        }

        $user->update([
            'is_blocked' => ! $user->is_blocked,
        ]);

        $message = $user->is_blocked
            ? "O usuário '{$user->name}' foi bloqueado com sucesso."
            : "O usuário '{$user->name}' foi desbloqueado com sucesso.";

        return redirect()->route('admin.users.index')->with('status', $message);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $currentUser = Auth::user();

        // Impedir auto-exclusão (Edge Case)
        if ($currentUser->id === $user->id) {
            return redirect()->route('admin.users.index')->with(
                'error',
                'Você não pode excluir a sua própria conta de administrador.'
            );
        }

        // Impedir exclusão do último administrador (FR-012)
        if ($user->isAdmin()) {
            $adminCount = User::where('role', 'admin')
                ->where('id', '!=', $user->id)
                ->count();

            if ($adminCount === 0) {
                return redirect()->route('admin.users.index')->with(
                    'error',
                    'Não é possível excluir este usuário porque ele é o único administrador cadastrado no sistema.'
                );
            }
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with(
            'status',
            "O usuário '{$name}' foi excluído com sucesso."
        );
    }
}
