@extends('layout')

@section('content')
<h2>CSV Preview</h2>
<table border="1" cellpadding="5">
    @foreach ($rows as $row)
        <tr>
            @foreach ($row as $cell)
                <td>{{ $cell }}</td>
            @endforeach
        </tr>
    @endforeach
</table>
<a href="{{ url()->previous() }}">Back</a>
@endsection