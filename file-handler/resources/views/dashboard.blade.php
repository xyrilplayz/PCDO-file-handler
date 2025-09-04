@extends('layout')
@section('title', 'Dashboard')
@section('content')
<div class="container">
    <h2>Select Program & Add Cooperative</h2>

    <form method="POST" action="{{ url('cooperatives') }}">
        @csrf

        <div class="mb-3">
            <label for="program_id">Choose Program</label>
            <select name="program_id" id="program_id" class="form-control">
                @foreach($programs as $program)
                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="name">Cooperative Name</label>
            <input type="text" name="name" class="form-control" placeholder="Enter cooperative name">
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
@endsection
