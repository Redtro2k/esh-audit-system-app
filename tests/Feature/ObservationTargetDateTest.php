<?php

use App\Models\ConcernCategory;
use App\Models\Department;
use App\Models\Observation;
use App\Models\User;

test('observation target date can be null', function () {
    $department = Department::query()->create(['name' => 'Operations']);
    $pic = User::factory()->create([
        'username' => 'pic-target-date-user',
        'department_id' => $department->id,
    ]);
    $auditor = User::factory()->create([
        'username' => 'auditor-target-date-user',
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

    expect($observation->fresh()->target_date)->toBeNull();
});
