<?php

namespace App\Policies;

use App\Models\Klien;
use App\Models\User;

class KlienPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->toko()->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Klien $klien): bool
    {
        return $user->toko->id === $klien->toko_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->toko()->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Klien $klien): bool
    {
        return $user->toko->id === $klien->toko_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Klien $klien): bool
    {
        return $user->toko->id === $klien->toko_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Klien $klien): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Klien $klien): bool
    {
        return false;
    }
}
