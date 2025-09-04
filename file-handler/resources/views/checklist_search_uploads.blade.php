@extends('layout')

@section('title', 'Search Uploads')

@section('content')
    <div class="container">
        <h2>Search & Filter Uploads</h2>

        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('uploads.search') }}" class="mb-4 row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search Cooperative Name"
                    value="{{ request('search') }}">
            </div>

            <div class="col-md-4">
                <select name="program_id" class="form-control">
                    <option value="">-- Filter by Program --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('uploads.search') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>

        <!-- Results -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Cooperative</th>
                    <th>Program</th>
                    <th>Checklist Item</th>
                    <th>File</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($uploads as $upload)
                    <tr>
                        <td>{{ $upload->cooperative->name }}</td>
                        <td>{{ $upload->cooperative->program->name ?? 'N/A' }}</td>
                        <td>{{ $upload->checklistItem->name ?? 'N/A' }}</td>
                        <td>{{ $upload->file_name }}</td>
                        <td>
                            <a href="{{ route('checklist.download', $upload->id) }}" class="btn btn-success btn-sm">
                                Download
                            </a>
                            <form action="{{ route('checklist.delete', $upload->id) }}" method="POST"
                                style="display:inline-block;"
                                onsubmit="return confirm('Are you sure you want to delete this file?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No uploads found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        {{ $uploads->withQueryString()->links() }}
    </div>
@endsection