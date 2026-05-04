<?php

use App\Filament\Resources\Observations\ObservationResource;
use App\Models\ConcernCategory;
use App\Models\Dealer;
use App\Models\Department;
use App\Models\Observation;
use App\Models\User;
use App\Support\AnalyticsObservationScope;
use Spatie\Permission\Models\Role;

test('remediator can see same dealer department observations while representative only sees own', function () {
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
    $representative->dealers()->attach($tne);

    $tnescRepresentative = User::factory()->create([
        'username' => 'tnesc-representative',
        'department_id' => $departmentMis->id,
    ]);
    $tnescRepresentative->assignRole('representative');
    $tnescRepresentative->dealers()->attach($tnesc);

    $directTnescPic = User::factory()->create([
        'username' => 'direct-tnesc-pic',
        'department_id' => $departmentMis->id,
    ]);
    $directTnescPic->assignRole('representative');
    $directTnescPic->dealers()->attach($tnesc);

    $otherRepresentative = User::factory()->create([
        'username' => 'ops-representative',
        'department_id' => $departmentOps->id,
    ]);
    $otherRepresentative->assignRole('representative');
    $otherRepresentative->dealers()->attach($opsDealer);

    $sameDealerOtherDepartmentRepresentative = User::factory()->create([
        'username' => 'tne-ops-representative',
        'department_id' => $departmentOps->id,
    ]);
    $sameDealerOtherDepartmentRepresentative->assignRole('representative');
    $sameDealerOtherDepartmentRepresentative->dealers()->attach($tne);

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
        'area' => 'TNE representative concern',
        'pic_id' => $representative->id,
        'dealer_id' => $tne->id,
        'status' => 'pending',
        'target_date' => null,
        'concern_type' => (string) $concernCategory->id,
        'concern' => 'Concern for same dealer representative',
        'auditor_id' => $auditor->id,
    ]);

    $tnescObservation = Observation::query()->create([
        'area' => 'TNESC concern',
        'pic_id' => $tnescRepresentative->id,
        'dealer_id' => $tnesc->id,
        'status' => 'pending',
        'target_date' => null,
        'concern_type' => (string) $concernCategory->id,
        'concern' => 'Concern for other dealer representative',
        'auditor_id' => $auditor->id,
    ]);

    $directTnescObservation = Observation::query()->create([
        'area' => 'Direct TNESC concern',
        'pic_id' => $directTnescPic->id,
        'dealer_id' => $tnesc->id,
        'status' => 'pending',
        'target_date' => null,
        'concern_type' => (string) $concernCategory->id,
        'concern' => 'Concern assigned directly in another dealer',
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

    $sameDealerOtherDepartmentObservation = Observation::query()->create([
        'area' => 'TNE Operations concern',
        'pic_id' => $sameDealerOtherDepartmentRepresentative->id,
        'dealer_id' => $tne->id,
        'status' => 'pending',
        'target_date' => null,
        'concern_type' => (string) $concernCategory->id,
        'concern' => 'Concern for same dealer but other department',
        'auditor_id' => $auditor->id,
    ]);

    $this->actingAs($remediator);
    $remediatorIds = ObservationResource::getEloquentQuery()->pluck('id')->all();

    expect($remediatorIds)
        ->toContain($remediatorObservation->id)
        ->toContain($representativeObservation->id)
        ->not->toContain($tnescObservation->id)
        ->not->toContain($directTnescObservation->id)
        ->not->toContain($sameDealerOtherDepartmentObservation->id)
        ->not->toContain($otherDepartmentObservation->id);

    $analyticsIds = AnalyticsObservationScope::query()->pluck('id')->all();

    expect($analyticsIds)
        ->toContain($remediatorObservation->id)
        ->toContain($representativeObservation->id)
        ->not->toContain($tnescObservation->id)
        ->not->toContain($directTnescObservation->id)
        ->not->toContain($sameDealerOtherDepartmentObservation->id)
        ->not->toContain($otherDepartmentObservation->id);

    $this->actingAs($representative);
    $representativeIds = ObservationResource::getEloquentQuery()->pluck('id')->all();

    expect($representativeIds)
        ->toContain($representativeObservation->id)
        ->not->toContain($remediatorObservation->id)
        ->not->toContain($sameDealerOtherDepartmentObservation->id)
        ->not->toContain($otherDepartmentObservation->id);
});
