<?php

namespace App\Support;

use App\Filament\Resources\Observations\ObservationResource;
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

            $query->where('dealer_id', $selectedDealerId);

            if ($user->hasAnyRole(['developer', 'gm'])) {
                return $query;
            }

            return ObservationResource::applyObservationVisibility($query, $user);
        }

        if ($user->hasAnyRole(['developer', 'gm'])) {
            return $query;
        }

        if ($dealerIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return ObservationResource::applyObservationVisibility(
            $query->whereIn('dealer_id', $dealerIds->all()),
            $user,
        );
    }

    public static function visibleDealerIds(): Collection
    {
        return Dealer::query()
            ->visibleTo(auth()->user())
            ->pluck('dealers.id');
    }
}
