@extends('layout')

@section('content')
    <div class="container mt-4">
        <h2>Notifications</h2>
        <ul>
            <p>Coop: {{ $notifications ?? 'Unknown' }}</p>
            @foreach($notifications as $notif)
                <p>{{ $notif->subject }}</p>
                <p>{{ $notif->body }}</p>

            @endforeach
        </ul>
    </div>
@endsection