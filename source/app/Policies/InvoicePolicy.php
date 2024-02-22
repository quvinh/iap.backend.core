<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class InvoicePolicy
{
    use HandlesAuthorization;

    private const slug = 'invoice';

    /**
     * Determine whether the user can search the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function search(User $user)
    {
        try {
            $slug = self::slug;
            $permissions = $user->role()->first()->getSlugPermissions();
            return in_array("$slug.search", $permissions);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        try {
            $slug = self::slug;
            $permissions = $user->role()->first()->getSlugPermissions();
            return in_array("$slug.create", $permissions);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user)
    {
        try {
            $slug = self::slug;
            $permissions = $user->role()->first()->getSlugPermissions();
            return in_array("$slug.update", $permissions);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user)
    {
        try {
            $slug = self::slug;
            $permissions = $user->role()->first()->getSlugPermissions();
            return in_array("$slug.delete", $permissions);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
    }
}
