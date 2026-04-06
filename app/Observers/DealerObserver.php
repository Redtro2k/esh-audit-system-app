<?php

namespace App\Observers;

use App\Models\Dealer;

class DealerObserver
{
    public function creating(Dealer $dealer): void
    {
        if (filled($dealer->created_by) || ! auth()->check()) {
            return;
        }

        $dealer->created_by = auth()->id();
    }
}
