@extends('layout')

@section('title', 'MOA Upload')

@section('content')
    <div class="container mt-4">
        <h2>MOAs for Cooperative: {{ $coopProgram->cooperative->name }}</h2>
        <p>Program: {{ $coopProgram->program->name }}</p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>File Name</th>
                    <th>Date Signed</th>
                    <th>Uploaded By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coopProgram->moas as $moa)
                    <tr>
                        <td>{{ $moa->id }}</td>
                        <td>{{ $moa->file_name }}</td>
                        <td>{{ $moa->date_signed }}</td>
                        <td>{{ $moa->uploaded_by }}</td>
                        <td>
                            <a href="{{ asset('storage/' . $moa->file_path) }}" target="_blank" class="btn btn-success btn-sm">
                                Download
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No MOAs uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection