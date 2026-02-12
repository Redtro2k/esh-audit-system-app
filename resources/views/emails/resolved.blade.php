@component('mail::message')
# Audit Issue Resolved

Good day,

The audit issue you submitted regarding
<b>{{ $observation->area }}</b>
under the <b>{{ $observation->pic->department->name }}</b> department, with <b>{{ $observation->pic->name }}</b> as the assigned PIC,

has now been **resolved**.

After review and necessary action, the matter has been addressed accordingly. If you believe further clarification or follow-up is required, please feel free to coordinate with the assigned department.

@component('mail::button', ['url' => route('filament.admin.resources.observations.view', $observation)])
View Observation
@endcomponent

Thank you for your cooperation.<br>
{{ config('app.name') }}
@endcomponent
