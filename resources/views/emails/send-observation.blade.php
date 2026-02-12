@component('mail::message')
# New Observation Submitted

Amazing Day!

Please be reminded that an audit concern has been raised for your
<b>{{ $observation->area }}</b>
under the <b>{{ $observation->pic->department->name }}</b>,
with <b>{{ $observation->pic->name }}</b> as the assigned PIC.

@component('mail::button', ['url' => route('filament.admin.resources.observations.view', $observation)])
View Observation
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
