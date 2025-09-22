@extends('layout')

@section('content')
<div class="container mt-4">
    <h2>Programs</h2>
    <div class="row">
        @foreach($programs as $program)
            <div class="col-sm-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $program->name }}</h5>
                        <ul>
                            @foreach($program->coopProgram as $coopProgram)
                                <li>
                                    {{ $coopProgram->cooperative->name }} 
                                    ({{ $coopProgram->program_status }})
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('old.show', $program->id) }}" class="btn btn-primary btn-sm">
                            Show
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
