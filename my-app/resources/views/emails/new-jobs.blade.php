<x-mail::message>
# New jobs added

@foreach ($applications as $application)
- **{{ $application->company }}** — {{ $application->role }}
@endforeach

<x-mail::button :url="config('app.url')">
View dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
