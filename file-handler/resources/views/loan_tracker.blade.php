@extends('layout')

@section('title', 'Loan Tracker')

@section('content')
    <div class="container mt-4">
        <h2>Loan Tracker for {{ $loan->program->name }}</h2>
        <h3>Cooperative: {{ $loan->cooperative->name }}</h3>
        <p><strong>Amount:</strong> ₱{{ number_format($coop->loan_ammount, 2) }}</p>
        <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($loan->start_date)->toFormattedDateString() }}</p>
        <p><strong>Grace Period:</strong> {{ $coop->with_grace }} months</p>
        <p><strong>Term:</strong> {{ $loan->program->term_months - $coop->with_grace }} months</p>
        <br>
        {{-- -if statement here
        if after 4 months then delinquent --}}
        <a class="btn btn-secondary" href="{{ route('amortization.download', $loan->id) }}">Download</a>

        <form action="{{ route('loan.incomplete', $loan->id) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-secondary">Nullified</button>
        </form>
        @if (!$coop->program_status)
            <form action="{{ route('resolved.store', $loan->id) }}" method="POST" enctype="multipart/form-data"
                style="display:inline;">
                @csrf
                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
                <button type="submit" class="btn btn-success">Resolved</button>
            </form>
        @endif


        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Due Date</th>
                    <th>Amount</th>
                    <th>Penalty</th>
                    <th>DUES</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Notify</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $carryOver = 0
                @endphp

                @foreach($loan->ammortizationSchedules as $schedule)
                    @php
                        // Compute total due with carry-over applied
                        $dueBase = $schedule->installment + $schedule->penalty_amount;
                        $totalDue = $dueBase + $carryOver - $schedule->amount_paid;
                        $previous = $loop->index > 0 ? $loan->ammortizationSchedules[$loop->index - 1] : null;

                        // Update carryOver for the next row
                        $carryOver = max(0, $totalDue);


                        $isOverdue_today = !$schedule->is_paid && $schedule->due_date->istoday();
                        $isOverdue = !$schedule->is_paid && $schedule->due_date->ispast();


                        $monthsOverdue = $isOverdue
                            ? \Carbon\Carbon::parse($schedule->due_date)->diffInMonths(now())
                            : 0;

                        // Penalty is always 1% per month overdue
                        $penaltyAmount = $monthsOverdue > 0
                            ? $schedule->amount_due * 0.01 * $monthsOverdue
                            : 0;
                    @endphp

                    <tr class="{{ $schedule->rowClass }}">
                        {{-- Due Date --}}
                        <td>{{ \Carbon\Carbon::parse($schedule->due_date)->toFormattedDateString() }}</td>

                        {{-- Amount --}}
                        <td>
                            ₱{{ number_format($schedule->installment, 2) }}
                            @if($schedule->penalty_amount > 0)
                                <br><small class="text-danger">
                                    Penalty: ₱{{ number_format($schedule->penalty_amount, 2) }}
                                </small>
                            @endif
                        </td>

                        {{-- Penalty --}}
                        <td>
                            @if(!$schedule->isPaid)
                                @if($schedule->penalty_amount == 0)
                                    ₱{{ number_format($schedule->installment + $schedule->penalty_amount, 2) }}
                                    <form action="{{ route('schedules.penalty', $schedule->id) }}" method="POST" class="mt-1">
                                        @csrf
                                        <input type="hidden" name="add" value="1">
                                        <button type="submit" class="btn btn-sm btn-danger">Add Penalty</button>
                                    </form>
                                @else
                                    ₱{{ number_format($schedule->installment + $schedule->penalty_amount, 2) }}
                                    <form action="{{ route('schedules.penalty', $schedule->id) }}" method="POST" class="mt-1">
                                        @csrf
                                        <input type="hidden" name="remove" value="1">
                                        <button type="submit" class="btn btn-sm btn-secondary">Remove Penalty</button>
                                    </form>
                                @endif
                            @endif
                        </td>

                        {{-- Dues (with carryover logic) --}}
                        <td>
                            ₱{{ number_format(max(0, $totalDue), 2) }}
                        </td>

                        {{-- Status --}}
                        <td>
                            @if($schedule->status === 'Resolved')
                                🟩 <strong>Resolved</strong>
                                <br>
                                {{ $schedule->date_paid ? \Carbon\Carbon::parse($schedule->date_paid)->toFormattedDateString() : 'N/A' }}
                            @elseif($schedule->status === 'Paid' || $schedule->is_paid)
                                ✅ Paid on
                                {{ $schedule->date_paid ? \Carbon\Carbon::parse($schedule->date_paid)->toFormattedDateString() : 'N/A' }}
                            @elseif($schedule->status === 'Partial Paid')
                                🟡 Partial Paid (₱{{ number_format($schedule->balance, 2) }} remaining)
                            @elseif($isOverdue_today)
                                ❗ Due Today ❗
                            @elseif(!$schedule->is_paid && $schedule->due_date->isPast())
                                ❌ Overdue (₱{{ number_format($schedule->balance, 2) }} unpaid)
                            @else
                                🔜 Next Due
                            @endif
                        </td>

                        {{-- Payment --}}
                        <td>
                            @if ($schedule->status === 'Resolved')
                                <span class="badge bg-success">Resolved</span>
                                <br>All payments settled.
                            @elseif ($schedule->status === 'Paid')
                                <span class="badge bg-success">Paid</span>
                                ₱{{ number_format($schedule->installment, 2) }}
                            @elseif ($schedule->status === 'Partial Paid')
                                <span class="badge bg-warning text-dark">Partial Paid</span>
                                ₱{{ number_format($schedule->installment, 2) }}
                                <br>
                                Amount Paid: ₱{{ number_format($schedule->amount_paid, 2) }}
                                <br>
                                Carried Over:
                                ₱{{ number_format(($schedule->installment + $schedule->penalty_amount) - $schedule->amount_paid, 2) }}
                            @else
                                {{-- Only show payment buttons if not resolved --}}
                                @if($loan->program_status !== 'Resolved')
                                    <form action="{{ route('schedules.markPaid', $schedule->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Mark Paid</button>
                                    </form>
                                    <form action="{{ route('schedules.post', $schedule->id) }}" method="POST" class="mt-1">
                                        @csrf
                                        <input type="number" step="0.01" name="amount_paid" value="{{ $schedule->amount_paid }}"
                                            class="form-control form-control-sm" placeholder="Amount paid">
                                        <button type="submit" class="btn btn-sm btn-warning mt-1">Update Payment</button>
                                    </form>
                                @else
                                    <span class="text-muted">Payment disabled (Resolved)</span>
                                @endif
                            @endif
                        </td>

                        {{-- Notify --}}
                        <td>
                            @if ($schedule->status !== 'Paid')
                                <form action="{{ route('schedules.sendOverdueEmail', $schedule->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Send Overdue Email</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection