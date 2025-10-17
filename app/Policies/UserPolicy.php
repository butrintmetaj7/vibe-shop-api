<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update(User $currentUser, User $targetUser, ?string $newRole = null): bool
    {
        if ($currentUser->id !== $targetUser->id) {
            return true;
        }

        if ($newRole && $newRole !== $targetUser->role && $currentUser->role === 'admin') {
            return false;
        }

        return true;
    }

    public function delete(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id !== $targetUser->id;
    }
}

