@extends('layout')

@section('title', 'Checklist Upload')

@section('content')
    <div class="container">
        <h2>Checklist for Cooperative: {{ $cooperative->cooperative->name }}</h2>
        <p>Program: {{ $cooperative->program->name }}</p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
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
                @php
                    $uploads = $item->uploads->where('coop_program_id', $cooperative->id);
                @endphp
                {{-- If uploaded, show download --}}
                @if($uploads->isNotEmpty())
                    <div class="mt-2">
                        @foreach($uploads as $upload)
                            <a href="{{ route('checklist.download', $upload->id) }}" class="btn btn-success btn-sm d-block mb-1">
                                Download (Availment #{{ $upload->coop_program_id }}) - {{ $upload->file_name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Only show finalize loan if all checklist items are uploaded --}}
        @php
            $total = $checklistItems->count();
            $uploaded = $checklistItems->filter(fn($i) => $i->uploads->isNotEmpty())->count();
        @endphp

        @if($total > 0 && $total > $uploaded)
            <div class="card my-4 p-3 border-success">
                <h4>Finalize Loan Details</h4>
                <form action="{{ route('program.finalizeLoan', $cooperative->id) }}" method="POST">
                    @csrf

                    <div class="form-group mt-3">
                        <label for="loan_amount">Loan Amount:</label>
                        <div class="input-group">
                            <input type="number" name="loan_ammount" id="loan_amount" class="form-control"
                                placeholder="Enter Loan Amount" required>
                            <button type="button" class="btn btn-outline-primary" id="use_min">Use Min</button>
                            <button type="button" class="btn btn-outline-success" id="use_max">Use Max</button>
                        </div>
                        <small id="loan_range" class="form-text text-muted"></small>
                    </div>

                    <div class="form-group mt-3">
                        <label for="with_grace">Grace Period:</label>
                        <select name="with_grace" id="with_grace" class="form-control" required>
                            <option value="0">Without Grace Period</option>
                            <option value="1">With Grace Period</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Finalize Loan</button>
                </form>
                {{-- JS for min/max buttons --}}
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        const loanInput = document.getElementById("loan_amount");
                        const loanRange = document.getElementById("loan_range");
                        const btnMin = document.getElementById("use_min");
                        const btnMax = document.getElementById("use_max");

                        let min = {{ $cooperative->program->min_amount }};
                        let max = {{ $cooperative->program->max_amount }};

                        loanRange.innerText = `Allowed range: ₱${min} - ₱${max}`;
                        loanInput.min = min;
                        loanInput.max = max;

                        btnMin.addEventListener("click", () => loanInput.value = min);
                        btnMax.addEventListener("click", () => loanInput.value = max);
                    });
                </script>
        @endif
        </div>
        @if($cooperative->loan_ammount && in_array($cooperative->with_grace, [0, 4]))
            <form action="{{ route('generate.create', $cooperative->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">Generate Amortization Schedule</button>
            </form>
        @endif
@endsection