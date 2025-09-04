@extends('layout')

@section('title', 'Checklist Upload')

@section('content')
    <div class="container">
        <h2>Checklist for Cooperative: {{ $cooperative->name }}</h2>
        <p>Program: {{ $cooperative->program->name }}</p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @foreach($checklistItems as $item)
            <div class="card my-3 p-3">
                <h5>{{ $item->name }}</h5>

                {{-- Upload form --}}
                <form action="{{ route('checklist.upload', $cooperative->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="checklist_item_id" value="{{ $item->id }}">
                    <input type="file" name="file" required>
                    <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                </form>


                {{-- If already uploaded, show download --}}
                @if($item->upload)
                    <a href="{{ route('checklist.download', $item->upload->id) }}" class="btn btn-success btn-sm mt-2">
                        Download {{ $item->upload->file_name }}
                    </a>
                @endif
            </div>
        @endforeach


        <a href="{{ route('uploads.search', $cooperative->id) }}" class="btn btn-secondary mt-3">
            View All Uploaded Files
        </a>
        @if($loan)
            <hr>
            <h4>Adjust Loan Amount</h4>
            <form action="{{ route('loans.updateAmount', $loan->id) }}" method="POST" class="mb-4">
                @csrf
                @method('PUT')

                <div class="mb-2">
                    <label>Loan Amount</label>
                    <input type="number" name="amount" class="form-control" value="{{ $loan->amount }}" step="0.01">
                </div>

                <div class="mb-2">
                    <label>Preset</label>
                    <select name="preset" class="form-select">
                        <option value="">-- Select --</option>
                        <option value="min">Min: {{ number_format($loan->program->min_amount, 2) }}</option>
                        <option value="max">Max: {{ number_format($loan->program->max_amount, 2) }}</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update Loan Amount</button>
            </form>
            <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-primary mt-3">
                View Loan
            </a>
        @endif


    </div>
@endsection