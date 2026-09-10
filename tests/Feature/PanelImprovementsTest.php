<?php

use App\Filament\Resources\Observations\ObservationResource;
use App\Filament\Resources\Observations\Pages\CreateObservation;
use App\Filament\Resources\Observations\Pages\PresentObservations;
use App\Filament\Widgets\LatestOngoing;
use App\Filament\Widgets\StatsOverview;
use App\Models\ConcernCategory;
use App\Models\Dealer;
use App\Models\Department;
use App\Models\Observation;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $department = Department::query()->create(['name' => 'Operations']);
    $this->auditor = User::factory()->create(['department_id' => $department->id]);
    $this->auditor->assignRole(Role::findOrCreate('auditor'));
    $this->dealer = Dealer::query()->create([
        'name' => 'Test Dealer', 'acronym' => 'TEST', 'created_by' => $this->auditor->id,
    ]);
    $this->auditor->dealers()->attach($this->dealer);
    $this->category = ConcernCategory::query()->create(['name' => 'Safety']);
    $this->actingAs($this->auditor);
    $this->makeObservation = fn (array $data = []) => Observation::query()->create([
        'area' => 'Warehouse',
        'dealer_id' => $this->dealer->id,
        'pic_id' => $this->auditor->id,
        'auditor_id' => $this->auditor->id,
        'concern_type' => (string) $this->category->id,
        'concern' => 'Check storage access',
        'status' => 'ongoing',
        ...$data,
    ]);
});

test('dashboard counts and timeline include the final day of the date filter', function () {
    ($this->makeObservation)(['area' => 'Included final evening', 'created_at' => '2026-09-10 23:59:59']);
    ($this->makeObservation)(['area' => 'Excluded next morning', 'created_at' => '2026-09-11 00:00:00']);
    $filters = ['startDate' => '2026-09-10', 'endDate' => '2026-09-10'];

    $widget = Livewire::test(StatsOverview::class, ['pageFilters' => $filters]);
    expect($widget->instance()->getStatusCounts()['ongoing'])->toBe(1);

    Livewire::test(LatestOngoing::class, ['pageFilters' => $filters])
        ->assertSee('Included final evening')
        ->assertDontSee('Excluded next morning');
});

test('presentation retrieves one record and clamps invalid slide positions', function () {
    foreach (range(1, 12) as $index) {
        ($this->makeObservation)(['area' => 'Area '.$index, 'created_at' => '2026-09-10 12:00:00']);
    }

    $page = new PresentObservations;
    expect($page->getPresentationCount())->toBe(12);
    $page->slide = 999;
    DB::enableQueryLog();
    $observation = $page->getCurrentObservation(12);
    $queries = collect(DB::getQueryLog());
    DB::disableQueryLog();

    expect($page->slide)->toBe(11)
        ->and($observation->area)->toBe('Area 12')
        ->and($queries->contains(fn ($query) => str_contains($query['query'], 'limit 1 offset 11')))->toBeTrue();

    $page->status = 'resolved';
    expect($page->getCurrentObservation())->toBeNull()->and($page->slide)->toBe(0);
});

test('mention indicators do not issue a separate comment query for every table row', function () {
    ($this->makeObservation)();
    ($this->makeObservation)();
    $records = ObservationResource::getEloquentQuery()->get();

    DB::enableQueryLog();
    foreach ($records as $record) {
        expect(ObservationResource::isMentionOnlyObservation($record))->toBeFalse();
    }
    expect(DB::getQueryLog())->toBeEmpty();
    DB::disableQueryLog();
});

test('observation drafts restore without creating records and stay private to their owner', function () {
    Livewire::test(CreateObservation::class)
        ->fillForm(['area' => 'Unfinished mobile audit', 'concern' => 'Draft concern'])
        ->call('autosave')
        ->assertDispatched('autosave-status', status: 'saved');

    Livewire::test(CreateObservation::class)
        ->assertSet('autosaveHasDraft', true)
        ->call('restoreDraft')
        ->assertFormSet(['area' => 'Unfinished mobile audit', 'concern' => 'Draft concern']);

    expect(Observation::query()->count())->toBe(0);
    $otherAuditor = User::factory()->create(['department_id' => $this->auditor->department_id]);
    $otherAuditor->assignRole('auditor');
    $otherAuditor->dealers()->attach($this->dealer);
    $this->actingAs($otherAuditor);

    Livewire::test(CreateObservation::class)->assertSet('autosaveHasDraft', false);
});

test('guests cannot access changelog data or the removed debug endpoint', function () {
    auth()->logout();
    $this->getJson('/shiplog/feed')->assertForbidden();
    $this->get('/test-mail')->assertNotFound();
});
