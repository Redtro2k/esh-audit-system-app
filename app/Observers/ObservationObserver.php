<?php

namespace App\Observers;

use App\Models\Observation;
use App\Support\ObservationAnalyticsCache;
use Illuminate\Support\Carbon;

class ObservationObserver
{
    public function creating(Observation $observation): void
    {
        $this->syncStatusDates($observation);
        $this->syncCounterMeasureDate($observation);
    }

    public function updating(Observation $observation): void
    {
        $this->syncStatusDates($observation);
        $this->syncCounterMeasureDate($observation);
    }

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

    protected function syncStatusDates(Observation $observation): void
    {
        $status = strtolower((string) $observation->status);

        if ($status === '') {
            return;
        }

        if (! $observation->exists || $observation->isDirty('status')) {
            $timestamp = Carbon::now('Asia/Manila');

            match ($status) {
                'pending' => $observation->date_pending = $timestamp,
                'ongoing' => $observation->date_ongoing = $timestamp,
                'for further discussion' => $observation->date_for_further_discussion = $timestamp,
                'resolved' => $observation->date_resolved = $timestamp,
                default => null,
            };
        }
    }

    protected function syncCounterMeasureDate(Observation $observation): void
    {
        if (! $observation->isDirty(['counter_measure', 'remarks', 'capture_solved'])) {
            return;
        }

        $hasCounterMeasureUpdate = filled(trim(strip_tags((string) $observation->counter_measure)))
            || filled(trim(strip_tags((string) $observation->remarks)))
            || filled($observation->capture_solved);

        $observation->counter_measure_date = $hasCounterMeasureUpdate
            ? Carbon::now('Asia/Manila')
            : null;
    }
}
