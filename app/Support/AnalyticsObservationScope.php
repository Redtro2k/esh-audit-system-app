<?php

namespace App\Support;

use App\Models\Dealer;
use App\Models\Observation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AnalyticsObservationScope
{
    public static function query(int|string|null $dealerId = null): Builder
    {
        $query = Observation::query();
        $user = auth()->user();
        $dealerIds = static::visibleDealerIds();
        $selectedDealerId = filled($dealerId) ? (int) $dealerId : null;

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($selectedDealerId) {
            if (! $user->hasAnyRole(['developer', 'gm']) && ! $dealerIds->contains($selectedDealerId)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('dealer_id', $selectedDealerId);
        }

        if ($user->hasAnyRole(['developer', 'gm'])) {
            return $query;
        }

        if ($dealerIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('dealer_id', $dealerIds->all());
    }

    public static function visibleDealerIds(): Collection
    {
        return Dealer::query()
            ->visibleTo(auth()->user())
            ->pluck('dealers.id');
    }
}
