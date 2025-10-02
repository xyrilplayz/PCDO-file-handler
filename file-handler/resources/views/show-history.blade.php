@extends('layout')

@section('title', 'Notifications')

@section('content')
<div class="container mt-4">
    <h2>Notifications</h2>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>Cooperative</th>
                <th>Email (Coop Program)</th>
                <th>Subject</th>
                <th>Received</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notifications as $notif)
                <tr onclick="window.location='{{ route('notifications.show', $notif->id) }}'" style="cursor:pointer;">
                    <td>{{ $notif->schedule->coopProgram->cooperative->name ?? 'Unknown' }}</td>
                    <td>{{ $notif->schedule->coopProgram->email ?? 'N/A' }}</td>
                    <td>{{ Str::limit($notif->subject, 40) }}</td>
                    <td>{{ $notif->created_at->format('M d, Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
