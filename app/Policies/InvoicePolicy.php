<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->role === 'owner';
    }

    public function create(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->role === 'owner';
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->role === 'owner';
    }
}