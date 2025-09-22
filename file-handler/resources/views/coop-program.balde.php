@extends('layout')

@section('content')
<h2>{{ $coopProgram->program->name }} - {{ $coopProgram->cooperative->name }}</h2>

<p><strong>Loan Amount:</strong> ₱{{ number_format($coopProgram->loan_ammount, 2) }}</p>
<p><strong>Start Date:</strong> {{ $coopProgram->start_date->format('Y-m-d') }}</p>
<p><strong>Term Months:</strong> {{ $coopProgram->program->term_months }}</p>
<p><strong>Grace Period:</strong> {{ $coopProgram->with_grace ? 'Yes' : 'No' }}</p>

<h3>Amortization Schedule</h3>
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Due Date</th>
            <th>Installment</th>
            <th>Date Paid</th>
            <th>Amount Paid</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($coopProgram->ammortizationSchedules->sortBy('due_date') as $schedule)
        <tr>
            <td>{{ $schedule->due_date->format('Y-m-d') }}</td>
            <td>{{ $schedule->installment }}</td>
            <td>{{ $schedule->date_paid?->format('Y-m-d') ?? '-' }}</td>
            <td>{{ $schedule->amount_paid ?? '-' }}</td>
            <td>{{ $schedule->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection