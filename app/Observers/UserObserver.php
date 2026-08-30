<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Cria automaticamente a categoria padrão "Outros" para o novo usuário (FR-028)
        Category::firstOrCreate([
            'user_id' => $user->id,
            'name' => 'Outros',
        ]);
    }
}
