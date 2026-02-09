@component('mail::message')
# ⚠️ Alert Notification

**Project:** {{ $projectName }}  
**Alert:** {{ $alert->name }}

{{ $message }}

@component('mail::button', ['url' => route('dashboard')])
View Dashboard
@endcomponent

If you'd like to adjust your alert settings, you can manage them from your project dashboard.

Thanks,  
ICMQTT Team
@endcomponent
