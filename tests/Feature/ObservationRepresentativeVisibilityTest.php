<?php

use App\Filament\Resources\Observations\ObservationResource;
use App\Models\ConcernCategory;
use App\Models\Dealer;
use App\Models\Department;
use App\Models\Observation;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('remediator can see same department representative observations while representative only sees own', function () {
    Role::findOrCreate('remediator');
    Role::findOrCreate('representative');

    $departmentMis = Department::query()->create(['name' => 'MIS']);
    $departmentOps = Department::query()->create(['name' => 'Operations']);

    $dealerCreator = User::factory()->create([
        'username' => 'dealer-creator-user',
        'department_id' => $departmentMis->id,
    ]);

    $tne = Dealer::query()->create([
        'acronym' => 'TNE',
        'name' => 'Toyota North Edsa',
        'created_by' => $dealerCreator->id,
    ]);

    $tnesc = Dealer::query()->create([
        'acronym' => 'TNESC',
        'name' => 'Toyota North Edsa Service Center',
        'created_by' => $dealerCreator->id,
    ]);

    $opsDealer = Dealer::query()->create([
        'acronym' => 'OPS',
        'name' => 'Operations Dealer',
        'created_by' => $dealerCreator->id,
    ]);

    $remediator = User::factory()->create([
        'username' => 'ferdinand-hipolito',
        'department_id' => $departmentMis->id,
    ]);
    $remediator->assignRole('remediator');
    $remediator->dealers()->attach($tne);

    $representative = User::factory()->create([
        'username' => 'niel-clyude-flores',
        'department_id' => $departmentMis->id,
    ]);
    $representative->assignRole('representative');
    $representative->dealers()->attach($tnesc);

    $otherRepresentative = User::factory()->create([
        'username' => 'ops-representative',
        'department_id' => $departmentOps->id,
    ]);
    $otherRepresentative->assignRole('representative');
    $otherRepresentative->dealers()->attach($opsDealer);

    $auditor = User::factory()->create([
        'username' => 'observation-auditor',
        'department_id' => $departmentMis->id,
    ]);

    $concernCategory = ConcernCategory::query()->create(['name' => 'Operations Risk']);

    $remediatorObservation = Observation::query()->create([
        'area' => 'TNE concern',
        'pic_id' => $remediator->id,
        'dealer_id' => $tne->id,
        'status' => 'pending',
        'target_date' => null,
        'concern_type' => (string) $concernCategory->id,
        'concern' => 'Concern for remediator',
        'auditor_id' => $auditor->id,
    ]);

    $representativeObservation = Observation::query()->create([
        'area' => 'TNESC concern',
        'pic_id' => $representative->id,
        'dealer_id' => $tnesc->id,
        'status' => 'pending',
        'target_date' => null,
        'concern_type' => (string) $concernCategory->id,
        'concern' => 'Concern for representative',
        'auditor_id' => $auditor->id,
    ]);

    $otherDepartmentObservation = Observation::query()->create([
        'area' => 'OPS concern',
        'pic_id' => $otherRepresentative->id,
        'dealer_id' => $opsDealer->id,
        'status' => 'pending',
        'target_date' => null,
        'concern_type' => (string) $concernCategory->id,
        'concern' => 'Concern for other department representative',
        'auditor_id' => $auditor->id,
    ]);

    $this->actingAs($remediator);
    $remediatorIds = ObservationResource::getEloquentQuery()->pluck('id')->all();

    expect($remediatorIds)
        ->toContain($remediatorObservation->id)
        ->toContain($representativeObservation->id)
        ->not->toContain($otherDepartmentObservation->id);

    $this->actingAs($representative);
    $representativeIds = ObservationResource::getEloquentQuery()->pluck('id')->all();

    expect($representativeIds)
        ->toContain($representativeObservation->id)
        ->not->toContain($remediatorObservation->id)
        ->not->toContain($otherDepartmentObservation->id);
});
