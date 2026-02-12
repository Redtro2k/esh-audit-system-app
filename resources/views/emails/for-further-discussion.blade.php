@component('mail::message')
# Audit Issue Update

Good day,

The audit issue you submitted regarding
<b>{{ $observation->area }}</b>
under the <b>{{ $observation->pic->department->name }}</b> department, with <b>{{ $observation->pic->name }}</b> as the assigned PIC,

has now been endorsed for further discussion and evaluation.

The concerned team is currently reviewing the matter to determine the appropriate action and resolution. You will be notified once updates or decisions are available.

@component('mail::button', ['url' => route('filament.admin.resources.observations.view', $observation)])
View Observation
@endcomponent

Thank you for your cooperation.<br>
{{ config('app.name') }}
@endcomponent
