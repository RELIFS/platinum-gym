<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('owner') && $user->can('view_financial_reports');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole('owner') && $user->can('view_financial_reports')) {
            return true;
        }

        return $invoice->payment?->member?->user_id === $user->id;
    }

    public function download(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole('owner') && $user->can('export_financial_reports')) {
            return true;
        }

        return $invoice->payment?->member?->user_id === $user->id && $user->can('download_own_invoice');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return false;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return false;
    }
}
