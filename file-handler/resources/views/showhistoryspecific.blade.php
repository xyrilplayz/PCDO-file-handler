@extends('layout')

@section('title', 'Notification Detail')

@section('content')
<div class="container mt-4">
    <h2>{{ $notification->subject ?? 'No Subject' }}</h2>

    <p><strong>From Coop:</strong> {{ $notification->schedule->coopProgram->cooperative->name ?? 'Unknown' }}</p>
    <p><strong>Email:</strong> {{ $notification->schedule->coopProgram->email ?? 'N/A' }}</p>
    <p><strong>Received:</strong> {{ $notification->created_at->format('M d, Y H:i') }}</p>

    <hr>

    <pre style="white-space: pre-wrap;">{{ $notification->body }}</pre>

    <a href="{{ route('notifications.index') }}" class="btn btn-secondary mt-3">← Back to Notifications</a>
</div>
@endsection
