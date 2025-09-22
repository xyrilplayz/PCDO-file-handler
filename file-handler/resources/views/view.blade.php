@extends('layout')

@section('content')
<div class="container mt-4">
    <h3>CSV File: {{ $record->coopProgram->cooperative->name }} - {{ $record->coopProgram->program->name }}</h3>

    <table class="table table-bordered">
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $col)
                        <td>{{ $col }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('old.download', $record->id) }}" class="btn btn-success">Download CSV</a>
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
</div>
@endsection
