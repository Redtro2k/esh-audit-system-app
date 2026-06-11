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

test('user with contributor and remediator roles can see both owned and remediator observations', function () {
    Role::findOrCreate('contributor');
    Role::findOrCreate('remediator');
    Role::findOrCreate('representative');

    $department = Department::query()->create(['name' => 'Service']);

    $dealerCreator = User::factory()->create([
        'username' => 'dual-role-dealer-creator',
        'department_id' => $department->id,
    ]);

    $dealer = Dealer::query()->create([
        'acronym' => 'SVC',
        'name' => 'Service Dealer',
        'created_by' => $dealerCreator->id,
    ]);

    $dualRoleUser = User::factory()->create([
        'username' => 'dual-role-user',
        'department_id' => $department->id,
    ]);
    $dualRoleUser->assignRole('contributor', 'remediator');
    $dualRoleUser->dealers()->attach($dealer);

    $representative = User::factory()->create([
        'username' => 'dual-role-representative',
        'department_id' => $department->id,
    ]);
    $representative->assignRole('representative');
    $representative->dealers()->attach($dealer);

    $otherContributor = User::factory()->create([
        'username' => 'other-contributor',
        'department_id' => $department->id,
    ]);

    $concernCategory = ConcernCategory::query()->create(['name' => 'Safety']);

    $ownedObservation = Observation::query()->create([
        'area' => 'Created by dual role user',
        'pic_id' => $representative->id,
        'dealer_id' => $dealer->id,
        'status' => 'pending',
        'target_date' => null,
        'concern_type' => (string) $concernCategory->id,
        'concern' => 'Contributor-owned concern',
        'auditor_id' => $dualRoleUser->id,
    ]);

    $remediatorObservation = Observation::query()->create([
        'area' => 'Assigned to dual role user',
        'pic_id' => $dualRoleUser->id,
        'dealer_id' => $dealer->id,
        'status' => 'pending',
        'target_date' => null,
        'concern_type' => (string) $concernCategory->id,
        'concern' => 'Remediator concern',
        'auditor_id' => $otherContributor->id,
    ]);

    $this->actingAs($dualRoleUser);

    $visibleIds = ObservationResource::getEloquentQuery()->pluck('id')->all();

    expect($visibleIds)
        ->toContain($ownedObservation->id)
        ->toContain($remediatorObservation->id);

    expect(ObservationResource::canUpdateObservation($ownedObservation))->toBeTrue();
    expect(ObservationResource::canUpdateObservation($remediatorObservation))->toBeTrue();
});

test('mentioned user can see subscribed observation outside their department scope', function () {
    Role::findOrCreate('remediator');
    Role::findOrCreate('representative');

    $misDepartment = Department::query()->create(['name' => 'MIS']);
    $operationsDepartment = Department::query()->create(['name' => 'Operations']);

    $dealerCreator = User::factory()->create([
        'username' => 'mention-dealer-creator',
        'department_id' => $misDepartment->id,
    ]);

    $misDealer = Dealer::query()->create([
        'acronym' => 'MIS',
        'name' => 'MIS Dealer',
        'created_by' => $dealerCreator->id,
    ]);

    $operationsDealer = Dealer::query()->create([
        'acronym' => 'OPS',
        'name' => 'Operations Dealer',
        'created_by' => $dealerCreator->id,
    ]);

    $mentionedUser = User::factory()->create([
        'username' => 'fah',
        'department_id' => $misDepartment->id,
    ]);
    $mentionedUser->assignRole('remediator');
    $mentionedUser->dealers()->attach($misDealer);

    $operationsRepresentative = User::factory()->create([
        'username' => 'operations-pic',
        'department_id' => $operationsDepartment->id,
    ]);
    $operationsRepresentative->assignRole('representative');
    $operationsRepresentative->dealers()->attach($operationsDealer);

    $auditor = User::factory()->create([
        'username' => 'mention-auditor',
        'department_id' => $operationsDepartment->id,
    ]);

    $concernCategory = ConcernCategory::query()->create(['name' => 'Mention Visibility']);

    $otherDepartmentObservation = Observation::query()->create([
        'area' => 'Operations concern mentioning FAH',
        'pic_id' => $operationsRepresentative->id,
        'dealer_id' => $operationsDealer->id,
        'status' => 'pending',
        'target_date' => null,
        'concern_type' => (string) $concernCategory->id,
        'concern' => 'Concern from another department',
        'auditor_id' => $auditor->id,
    ]);

    $this->actingAs($mentionedUser);

    expect(ObservationResource::getEloquentQuery()->pluck('id')->all())
        ->not->toContain($otherDepartmentObservation->id);

    $otherDepartmentObservation->comment(
        'Please check this <span data-type="mention" data-id="'.$mentionedUser->id.'">@Ferdie Hipolito</span>.',
        $auditor,
    );

    expect(ObservationResource::getEloquentQuery()->pluck('id')->all())
        ->toContain($otherDepartmentObservation->id);

    expect(ObservationResource::isMentionOnlyObservation($otherDepartmentObservation))->toBeTrue();
    expect(ObservationResource::getNavigationBadge())->toBe('1 Pending');
});
