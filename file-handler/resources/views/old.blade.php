@extends('layout')

@section('content')
<h2>Saved CSVs</h2>
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>ID</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($files as $file)
        <tr>
            <td>{{ $file->id }}</td>
            <td>{{ $file->created_at }}</td>
            <td>
                <a href="{{ route('old.view', $file->id) }}">View</a> |
                <a href="{{ route('old.download', $file->id) }}">Download</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection