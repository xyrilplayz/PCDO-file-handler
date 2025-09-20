@extends('layout')

@section('title', 'Checklist Upload')

@section('content')
    <div class="container">
        <h2>Checklist for Cooperative: {{ $cooperative->cooperative->name }}</h2>
        <p>Program: {{ $cooperative->program->name }}</p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @foreach($checklistItems as $item)
            <div class="card my-3 p-3">
                <h5>{{ $item->name }}</h5>

                <form action="{{ route('checklist.upload', $cooperative->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="program_checklist_id" value="{{ $item->id }}">
                    <input type="file" name="file" required>
                    <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                </form>

                {{-- If uploaded, show download --}}
                @if($item->uploads->isNotEmpty())
                    <div class="mt-2">
                        @foreach($item->uploads as $upload)
                            <a href="{{ route('checklist.download', $upload->id) }}" class="btn btn-success btn-sm d-block mb-1">
                                Download (Availment #{{ $upload->coop_program_id }}) - {{ $upload->file_name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
        <form action="{{ route('generate.create', $cooperative->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">Generate Amortization Schedule</button>
        </form>
@endsection