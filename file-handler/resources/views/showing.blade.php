@extends('layout')

@section('content')
<div class="container mt-4">
    <h2>{{ $program->name }}</h2>

    <h4>Ongoing Cooperatives</h4>
    <ul>
        @forelse($ongoing as $coopProgram)
            <li>
                {{ $coopProgram->cooperative->name }}
                @foreach($coopProgram->olds as $file)
                    <a href="{{ route('old.view', $file->id) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('old.download', $file->id) }}" class="btn btn-sm btn-success">Download</a>
                @endforeach
            </li>
        @empty
            <li>No ongoing cooperatives.</li>
        @endforelse
    </ul>

    <h4>Finished Cooperatives</h4>
    <ul>
        @forelse($finished as $coopProgram)
            <li>
                {{ $coopProgram->cooperative->name }}
                @foreach($coopProgram->olds as $file)
                    <a href="{{ route('old.view', $file->id) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('old.download', $file->id) }}" class="btn btn-sm btn-success">Download</a>
                @endforeach
            </li>
        @empty
            <li>No finished cooperatives.</li>
        @endforelse
    </ul>

    <a href="{{ route('old.index') }}" class="btn btn-secondary mt-3">Back to Programs</a>
</div>
@endsection
