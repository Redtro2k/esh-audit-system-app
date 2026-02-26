@component('mail::message')
# Audit Issue Resolved

Amazing Day!

We Would like to inform you that the ESH(Environment, Safety, Health) concern in
your department has been successfully resolved.

Thank you for your cooperation and commitment to maintaining a safe and healthy workplace.

Best regards,<br>
DESH Team

@component('mail::button', ['url' => route('filament.admin.resources.observations.view', $observation)])
View Observation
@endcomponent

Thank you for your cooperation.<br>
{{ config('app.name') }}
@endcomponent
