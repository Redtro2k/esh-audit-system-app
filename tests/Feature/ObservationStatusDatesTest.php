<?php

use App\Models\ConcernCategory;
use App\Models\Department;
use App\Models\Observation;
use App\Models\User;
use Carbon\Carbon;

test('observation status dates are tracked automatically', function () {
    Carbon::setTestNow(Carbon::parse('2026-04-14 09:00:00', 'Asia/Manila'));

    $department = Department::query()->create(['name' => 'Operations']);
    $pic = User::factory()->create([
        'username' => 'pic-status-date-user',
        'department_id' => $department->id,
    ]);
    $auditor = User::factory()->create([
        'username' => 'auditor-status-date-user',
        'department_id' => $department->id,
    ]);
    $concernCategory = ConcernCategory::query()->create(['name' => 'Operations Risk']);

    $observation = Observation::query()->create([
        'area' => 'Warehouse',
        'pic_id' => $pic->id,
        'status' => 'pending',
        'target_date' => null,
        'concern_type' => (string) $concernCategory->id,
        'concern' => 'Stock discrepancy',
        'auditor_id' => $auditor->id,
    ]);

    expect($observation->fresh()->date_pending)->not->toBeNull();
    expect($observation->fresh()->date_ongoing)->toBeNull();
    expect($observation->fresh()->date_for_further_discussion)->toBeNull();
    expect($observation->fresh()->date_resolved)->toBeNull();

    Carbon::setTestNow(Carbon::parse('2026-04-14 10:00:00', 'Asia/Manila'));
    $observation->update(['status' => 'ongoing']);

    Carbon::setTestNow(Carbon::parse('2026-04-14 11:00:00', 'Asia/Manila'));
    $observation->update(['status' => 'for further discussion']);

    Carbon::setTestNow(Carbon::parse('2026-04-14 12:00:00', 'Asia/Manila'));
    $observation->update(['status' => 'resolved']);

    $observation->refresh();

    expect($observation->date_ongoing?->format('Y-m-d H:i:s'))->toBe('2026-04-14 10:00:00');
    expect($observation->date_for_further_discussion?->format('Y-m-d H:i:s'))->toBe('2026-04-14 11:00:00');
    expect($observation->date_resolved?->format('Y-m-d H:i:s'))->toBe('2026-04-14 12:00:00');

    Carbon::setTestNow();
});
