<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\AttachmentController;
use App\Http\Controllers\User\CategoryController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ExpenseController;
use App\Http\Controllers\User\RecurringExpenseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Rotas do Usuário Autenticado e Verificado
Route::middleware(['auth', 'verified'])->group(function () {
    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gastos Não Recorrentes (US3, US6)
    Route::resource('expenses', ExpenseController::class)->except(['show']);

    // Gastos Recorrentes (US4)
    Route::patch('recurring-expenses/{recurring_expense}/toggle-active', [RecurringExpenseController::class, 'toggleActive'])
        ->name('recurring-expenses.toggle-active');
    Route::resource('recurring-expenses', RecurringExpenseController::class);

    // Anexos (Documentos e Comprovantes) (FR-016, FR-018, FR-024)
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])
        ->name('attachments.destroy');

    // Categorias Próprias (US7)
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
});

// Rotas Administrativas (US2)
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/toggle-block', [UserManagementController::class, 'toggleBlock'])->name('users.toggle-block');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
