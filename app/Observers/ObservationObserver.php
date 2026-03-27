<?php

namespace App\Observers;

use App\Models\Observation;
use App\Support\ObservationAnalyticsCache;

class ObservationObserver
{
    public function created(Observation $observation): void
    {
        ObservationAnalyticsCache::flush();
    }

    public function updated(Observation $observation): void
    {
        ObservationAnalyticsCache::flush();
    }

    public function deleted(Observation $observation): void
    {
        ObservationAnalyticsCache::flush();
    }

    public function restored(Observation $observation): void
    {
        ObservationAnalyticsCache::flush();
    }

    public function forceDeleted(Observation $observation): void
    {
        ObservationAnalyticsCache::flush();
    }
}
