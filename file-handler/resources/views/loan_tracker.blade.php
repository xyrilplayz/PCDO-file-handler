@extends('layout')

@section('title', 'Loan Tracker')

@section('content')
    <div class="container mt-4">

        <h2>Loan Tracker for {{ $loan->program->name }}</h2>
        <h3>Cooperative: {{ $loan->cooperative->name }}</h3>

        <p><strong>Amount:</strong> ₱{{ number_format($loan->loan_ammount) }}</p>
        <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($loan->start_date)->toFormattedDateString() }}</p>
        <p><strong>Grace Period:</strong> {{ $loan->with_grace }} month(s)</p>

        @php
            $paymentStart = \Carbon\Carbon::parse($loan->start_date)->addMonthsNoOverflow($loan->with_grace);
        @endphp
        <p><strong>Payment Starts On:</strong> {{ $paymentStart->toFormattedDateString() }}</p>
        <p><strong>Term:</strong> {{ $loan->program->term_months }} months</p>
        <br>

        <a class="btn btn-secondary" href="{{ route('amortization.download', $loan->id) }}">Download</a>


        <form method="POST" action="{{ route('onetap.pay', $loan->id) }}" enctype="multipart/form-data">
            @csrf

            <input type="file" id="oneTapFile" name="receipt_image" hidden required>

            <button type="button" onclick="document.getElementById('oneTapFile').click()">
                OneTap
            </button>
        </form>

        <script>
            document.getElementById('oneTapFile').addEventListener('change', function () {
                this.closest('form').submit();
            });
        </script>


        <form action="{{ route('loan.incomplete', $loan->id) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-secondary">Nullified</button>
        </form>

        @if (!$loan->program_status)
            <form action="{{ route('resolved.store', $loan->id) }}" method="POST" enctype="multipart/form-data"
                style="display:inline;">
                @csrf
                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required>
                <button class="btn btn-success">Resolved</button>
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
                @foreach($loan->ammortizationSchedules as $schedule)
                    @php
                        $isPaid = in_array($schedule->status, ['Paid', 'Resolved']);
                        $isPartial = $schedule->status === 'Partial Paid';
                        $isToday = !$isPaid && $schedule->due_date->isToday();
                        $isOverdue = !$isPaid && $schedule->due_date->isPast();

                        // DUES calculation

                        $dues = $schedule->installment + $schedule->penalty_amount;
                        $prevSchedule = $loop->index > 0 ? $loan->ammortizationSchedules[$loop->index - 1] : null;
                        if ($prevSchedule && $prevSchedule->status === 'Partial Paid' && $prevSchedule->balance > 0) {
                            $dues += $prevSchedule->balance;
                        }
                    @endphp

                    <tr>
                        <td>{{ $schedule->due_date->toFormattedDateString() }}</td>
                        <td>₱{{ number_format($schedule->installment) }}</td>
                        <td>
                            @if (!$isPaid && !$isPartial)
                                @if ($schedule->penalty_amount == 0)
                                    <form action="{{ route('schedules.penalty', $schedule->id) }}" method="POST" class="mt-1">
                                        @csrf
                                        <input type="hidden" name="add" value="1">
                                        <button class="btn btn-sm btn-danger">Add Penalty</button>
                                    </form>
                                @else
                                    <form action="{{ route('schedules.penalty', $schedule->id) }}" method="POST" class="mt-1">
                                        @csrf
                                        <input type="hidden" name="remove" value="1">
                                        <button class="btn btn-sm btn-secondary">Remove Penalty</button>
                                    </form>
                                @endif
                            @endif
                        </td>

                        {{-- DUES --}}
                        <td>
                            @if($isPartial)
                                <span class="badge bg-success">Amount paid</span>
                                ₱{{ number_format($schedule->amount_paid) }}
                                <span class="badge bg-warning">Balance</span> ₱{{ number_format($schedule->balance) }}
                            @elseif ($isPaid)
                                <span class="badge bg-success">Amount Paid</span> ₱{{ number_format($schedule->amount_paid) }}
                            @else
                                ₱{{ number_format($schedule->installment + $schedule->balance) }}
                            @endif
                        </td>

                        <td>
                            @if($schedule->status === 'Resolved') 🟩 Resolved
                            @elseif($isPaid) ✅ Paid
                            @elseif($isPartial) 🟡 Partial Paid
                            @elseif($isToday) ❗ Due Today ❗
                            @elseif($isOverdue) ❌ Overdue
                            @else 🔜 Next Due
                            @endif
                        </td>

                        <td>
                            @if(!$isPaid && !$isPartial && !$isOverdue)
                                <form action="{{ route('schedules.markPaid', $schedule->id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <label class="form-label small">Upload Receipt:</label>
                                    <input type="file" name="receipt_image" required class="form-control form-control-sm mb-1">
                                    <button class="btn btn-sm btn-primary w-100">Mark as Paid</button>
                                </form>

                                <form action="{{ route('schedules.post', $schedule->id) }}" method="POST"
                                    enctype="multipart/form-data" class="mt-2">
                                    @csrf
                                    <input type="number" step="0.01" name="amount_paid" required
                                        class="form-control form-control-sm mb-1" placeholder="Enter amount">
                                    <label class="form-label small">Upload Receipt:</label>
                                    <input type="file" name="receipt_image" required class="form-control form-control-sm mb-1">
                                    <button class="btn btn-sm btn-warning w-100">Note Payment</button>
                                </form>
                            @elseif($isPartial)
                                <span class="badge bg-warning">Partial Paid</span>
                            @elseif ($isOverdue)
                                <span class="badge bg-danger">Overdue</span>
                            @else
                                <span class="badge bg-success">Paid</span>
                            @endif
                        </td>

                        <td>
                            @if(!$isPaid && !$isPartial)
                                <form action="{{ route('schedules.sendOverdueEmail', $schedule->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-danger">Send Overdue Email</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection