<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function update(User $currentUser, User $targetUser, ?string $newRole = null): Response|bool
    {
        if ($currentUser->id !== $targetUser->id) {
            return true;
        }

        if ($newRole && $newRole !== $targetUser->role && $currentUser->role === 'admin') {
            return Response::deny('You cannot change your own role');
        }

        return true;
    }

    public function delete(User $currentUser, User $targetUser): Response|bool
    {
        if ($currentUser->id !== $targetUser->id) {
            return true;
        }
        
        return Response::deny('You cannot delete your own account');
    }
}

