@extends('layout')

@section('title', 'Progress Reports')

@section('content')
<div class="container mt-4">
    <h2>Progress Reports for {{ $program->name }}</h2>

    {{-- Upload Form --}}
    <form action="{{ route('coop-progress.store', $program->id) }}" method="POST" enctype="multipart/form-data" class="mb-5">
        @csrf

        <div class="mb-3">
            <label for="coop_program_id" class="form-label">Select Cooperative</label>
            <select name="coop_program_id" id="coop_program_id" class="form-select" required>
                @foreach ($coopPrograms as $coopProgram)
                    <option value="{{ $coopProgram->id }}">{{ $coopProgram->cooperative->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="title" class="form-label">Report Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="file" class="form-label">Upload Images (you can select multiple)</label>
            <input type="file" name="file[]" class="form-control" multiple accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Upload Report</button>
    </form>

    <hr>

    {{-- Show existing progress reports --}}
    <h3>Uploaded Reports</h3>
    <div class="row">
        @foreach ($program->coopPrograms as $coopProgram)
            @foreach ($coopProgram->progressReports ?? [] as $report)
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <h5>{{ $report->title }}</h5>
                            <p class="small text-muted">{{ $coopProgram->cooperative->name }}</p>

                            {{-- Display the collage or single image --}}
                            <img src="{{ route('progress-reports.show', $report->id) }}" 
                                 alt="Progress Image" 
                                 class="img-fluid rounded mb-2" 
                                 style="max-height: 200px; object-fit: cover;">

                            <p>{{ $report->description }}</p>
                            <a href="{{ route('coop-progress.download', $report->id) }}" class="btn btn-sm btn-success">Download</a>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach
    </div>
</div>
@endsection
