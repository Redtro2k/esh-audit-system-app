<?php

namespace App\Support;

use App\Models\Observation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AnalyticsObservationScope
{
    public static function query(): Builder
    {
        $query = Observation::query();
        $user = auth()->user();
        $dealerIds = static::visibleDealerIds();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('developer')) {
            return $query;
        }

        if ($dealerIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('dealer_id', $dealerIds->all());
    }

    public static function visibleDealerIds(): Collection
    {
        return auth()->user()?->dealers()->pluck('dealers.id') ?? collect();
    }
}
