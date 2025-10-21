@extends('layout')

@section('title', 'Add Cooperative')

@section('content')
<div class="container mt-4">
    <h2>Add New Cooperative</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('cooperatives.post') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="id" class="form-label">Registration ID</label>
            <input type="text" class="form-control" id="id" name="id" required value="{{ old('id') }}">
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Cooperative Name</label>
            <input type="text" class="form-control" id="name" name="name" required value="{{ old('name') }}">
        </div>

        <div class="mb-3">
            <label for="holder" class="form-label">Holder (Parent Cooperative)</label>
            <select name="holder" id="holder" class="form-select">
                <option value="">-- None --</option>
                @foreach ($cooperatives as $coop)
                    <option value="{{ $coop->id }}" {{ old('holder') == $coop->id ? 'selected' : '' }}>
                        {{ $coop->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <select name="type" id="type" class="form-select" required>
                <option value="primary" {{ old('type') == 'primary' ? 'selected' : '' }}>Primary</option>
                <option value="secondary" {{ old('type') == 'secondary' ? 'selected' : '' }}>Secondary</option>
                <option value="tertiary" {{ old('type') == 'tertiary' ? 'selected' : '' }}>Tertiary</option>
            </select>
        </div>

        {{-- ✅ New fields --}}
        <div class="mb-3">
            <label for="contact_number" class="form-label">Contact Number</label>
            <input type="text" class="form-control" id="contact_number" name="contact_number" required value="{{ old('contact_number') }}">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required value="{{ old('email') }}">
        </div>

        <button type="submit" class="btn btn-success">Save Cooperative</button>
        <a href="{{ route('home') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
