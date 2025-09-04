@extends('layout')

@section('content')
<div class="container">
    <h2>Cooperative Already Exists</h2>
    <p>
        The cooperative <strong>{{ $cooperative->name }}</strong> 
        already exists under this program.
    </p>

    <a href="{{ route('checklist.show', $cooperative->id) }}" class="btn btn-primary">
        Go to Checklist
    </a>

    <a href="{{ route('cooperatives.create') }}" class="btn btn-secondary">
        Go Back
    </a>
</div>
@endsection
