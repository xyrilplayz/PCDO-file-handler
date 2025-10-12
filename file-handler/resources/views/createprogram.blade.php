@extends('layout')

@section('title', 'Enroll Cooperative in Program')

@section('content')
    <div class="container">
        <h2>Enroll Cooperative in Program</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('program.store') }}" method="POST">
            @csrf

            {{-- Cooperative --}}
            <div class="form-group mt-3">
                <label for="coop_id">Select Cooperative:</label>
                <select name="coop_id" id="coop_id" class="form-control" required>
                    <option value="">-- Select Cooperative --</option>
                    @foreach($cooperatives as $coop)
                        <option value="{{ $coop->id }}">{{ $coop->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Coop Email --}}
            <div class="form-group mt-3">
                <label for="coop_email">Cooperative Email:</label>
                <input type="email" name="coop_email" id="coop_email" class="form-control"
                    placeholder="Enter Cooperative Email" required>
            </div>

            {{-- -Coop number --}}
            <div class="form-group mt-3">
                <label for="number">Contact Number:</label>
                <input type="tel" name="number" id="number" class="form-control" placeholder="Enter Contact Number"
                    required>
            </div>

            {{-- Program --}}
            <div class="form-group mt-3">
                <label for="program_id">Select Program:</label>
                <select name="program_id" id="program_id" class="form-control" required>
                    <option value="">-- Select Program --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">
                            {{ $program->name }} (₱{{ number_format($program->min_amount) }} -
                            ₱{{ number_format($program->max_amount) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mt-3">
                <label for="project" class="form-label">Project Name</label>
                <input type="text" class="form-control" id="project" name="project" required value="{{ old('name') }}">
            </div>

            <button type="submit" class="btn btn-primary mt-3">Enroll</button>
        </form>
    </div>

    {{-- Optional: Add Select2 for typing/searchable cooperative --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#coop_id').select2({
                placeholder: '-- Select Cooperative --',
                allowClear: true
            });
        });
    </script>
@endsection