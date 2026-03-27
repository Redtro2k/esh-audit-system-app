<?php

use App\Models\ConcernCategory;
use App\Models\Department;
use App\Models\Observation;
use App\Models\User;
use App\Support\ObservationAnalyticsCache;
use Illuminate\Support\Facades\Cache;

test('observation analytics cache is invalidated after observation changes', function () {
    config(['cache.default' => 'array']);
    Cache::flush();

    $department = Department::query()->create(['name' => 'Operations']);
    $pic = User::factory()->create([
        'username' => 'pic-user',
        'department_id' => $department->id,
    ]);
    $auditor = User::factory()->create([
        'username' => 'auditor-user',
        'department_id' => $department->id,
    ]);
    $concernCategory = ConcernCategory::query()->create(['name' => 'Operations Risk']);

    $cachedCount = fn () => ObservationAnalyticsCache::remember(
        'test-count',
        ['scope' => 'all-observations'],
        now()->addMinutes(10),
        fn () => Observation::query()->count()
    );

    expect($cachedCount())->toBe(0);

    Observation::query()->create([
        'area' => 'Warehouse',
        'pic_id' => $pic->id,
        'status' => 'pending',
        'target_date' => now()->addWeek(),
        'concern_type' => (string) $concernCategory->id,
        'concern' => 'Stock discrepancy',
        'counter_measure' => 'Cycle count',
        'auditor_id' => $auditor->id,
        'date_captured' => now(),
        'remarks' => 'Requires review',
    ]);

    expect($cachedCount())->toBe(1);
});
