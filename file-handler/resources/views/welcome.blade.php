@extends('layout')

@section('title', 'HomePage')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Cooperatives</h2>
        </div>

        <div><a href="{{ route('program.create') }}" class="btn btn-primary align-items-center mb-3">
                + Add Program
            </a>
        </div>

        <div><a href="{{ route('old.index') }}" class="btn btn-primary align-items-center mb-3">
                View Done Programs
            </a>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#id</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Availed Program</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cooperatives as $index => $coop)
                    <tr>
                        <td>{{ $coop->id }}</td>
                        <td>{{ $coop->name }}</td>
                        <td>{{ $coop->type }}</td>

                        <td>
                            <a href="{{ route('cooperatives.show', $coop->id) }}" class="btn btn-primary">
                                View
                            </a>
                            @foreach($coop->coopProgram as $coopProgram)
                                <a href="{{ route('checklists.show', $coopProgram->id) }}" class="btn btn-secondary mt-1">
                                    View checklist for {{ $coopProgram->program->name }}
                                </a>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">No cooperatives found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection