<?php

namespace App\Actions;

use App\Models\User;

class CompleteProfile
{
    public function handel(User $user, array $attributes)
    {
        $data = collect($attributes)->only([
            'name',
            'email', 
            'active'
        ])->toArray();

        $user->update($data);
    }
}
