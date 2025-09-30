@extends('layout')

@section('content')
<div class="container mt-4">
    <h3>Add Documentation for {{ $program->name }}</h3>

    <form action="{{ route('progress.store', $program->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group mt-3">
            <label for="coop_program_id">Select Cooperative:</label>
            <select name="coop_program_id" id="coop_program_id" class="form-control" required>
                <option value="">-- Select Cooperative --</option>
                @foreach($coopPrograms as $coopProgram)
                    <option value="{{ $coopProgram->id }}">
                        {{ $coopProgram->cooperative->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mt-3">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>

        <div class="form-group mt-3">
            <label for="description">Description:</label>
            <textarea name="description" id="description" class="form-control"></textarea>
        </div>

        <div class="form-group mt-3">
            <label for="file">Upload File:</label>
            <input type="file" name="file" id="file" class="form-control">
        </div>

        <button type="submit" class="btn btn-success mt-3">Save Documentation</button>
    </form>
</div>
@endsection
