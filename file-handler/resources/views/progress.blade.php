@extends('layout')

@section('title', 'Cooperative Details')

@section('content')
<div class="container">
    <h2>{{ $cooperative->name }}</h2>
    <p>Type: {{ ucfirst($cooperative->type) }}</p>

    <h3>Ongoing Programs</h3>
    <ul>
        @php
            $ongoingPrograms = $cooperative->coopProgram->where('program_status', 'Ongoing');
        @endphp
        @forelse($ongoingPrograms as $program)
            <li>
                {{ $program->program->name }} - Status: {{ $program->program_status }}

                {{-- Show CSVs for this ongoing program --}}
                @if($program->olds && $program->olds->isNotEmpty())
                    <ul>
                        @foreach($program->olds as $old)
                            <li>
                                {{ $old->file_name ?? 'CSV File' }}
                                <a href="{{ route('old.download', $old->id) }}" class="btn btn-sm btn-success">Download</a>
                                <a href="{{ route('old.view', $old->id) }}" class="btn btn-sm btn-primary">View</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @empty
            <li>No ongoing programs</li>
        @endforelse
    </ul>

    <h3>Old Programs (Finished)</h3>
    <ul>
        @php
            $finishedPrograms = $cooperative->coopProgram->where('program_status', 'Finished');
        @endphp
        @forelse($finishedPrograms as $program)
            <li>
                {{ $program->program->name ?? 'N/A' }} - Finished on {{ $program->end_date ?? 'N/A' }}

                {{-- Show CSVs for this finished program --}}
                @if($program->olds && $program->olds->isNotEmpty())
                    <ul>
                        @foreach($program->olds as $old)
                            <li>
                                {{ $old->file_name ?? 'File' }}
                                <a href="{{ route('old.download', $old->id) }}" class="btn btn-sm btn-success">Download</a>
                                <a href="{{ route('old.view', $old->id) }}" class="btn btn-sm btn-primary">View</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>No file uploaded yet.</p>
                @endif
            </li>
        @empty
            <li>No finished programs</li>
        @endforelse
    </ul>

    <h3>Progress Reports</h3>
    <ul>
        @forelse($cooperative->progressReports as $progress)
            <li>
                <strong>{{ $progress->title }}</strong> - {{ $progress->description }}
                @if($progress->file_name)
                <a href="{{ route('progress.show', $progress->id) }}"class="btn btn-sm btn-danger">
                        show</a>
                    <a href="{{ route('progress.download', $progress->id) }}" class="btn btn-sm btn-success">
                        Download {{ $progress->file_name }}
                    </a>
                @endif
            </li>
        @empty
            <li>No progress reports uploaded yet</li>
        @endforelse
    </ul>
</div>
@endsection