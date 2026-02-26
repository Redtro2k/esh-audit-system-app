@component('mail::message')
# New Observation Submitted

Amazing Day!

This is a reminder that you have a pending ESH(Environment, Safety, Health),
concern that requires you immediate attention.

Please take necessary action to provide an update within the given timeframe.

Thank you for your cooperation.

Best Regards,<br>
DESH Team

@component('mail::button', ['url' => route('filament.admin.resources.observations.view', $observation)])
View Observation
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
