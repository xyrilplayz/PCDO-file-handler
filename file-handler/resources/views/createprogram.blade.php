@extends('layout')

@section('title', 'Enroll Cooperative in Program')

@section('content')
    <div class="container">
        <h2>Enroll Cooperative in Program</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('program.store') }}" method="POST">
            @csrf

            {{-- Cooperative --}}
            <div class="form-group mt-3">
                <label for="coop_id">Select Cooperative:</label>
                <select name="coop_id" id="coop_id" class="form-control" required>
                    <option value="">-- Select Cooperative --</option>
                    @foreach($cooperatives as $coop)
                        <option value="{{ $coop->id }}">{{ $coop->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Coop Email --}}
            <div class="form-group mt-3">
                <label for="coop_email">Cooperative Email:</label>
                <input type="email" name="coop_email" id="coop_email" class="form-control"
                    placeholder="Enter Cooperative Email" required>
            </div>

            {{-- Program --}}
            <div class="form-group mt-3">
                <label for="program_id">Select Program:</label>
                <select name="program_id" id="program_id" class="form-control" required>
                    <option value="">-- Select Program --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" data-min="{{ $program->min_amount }}"
                            data-max="{{ $program->max_amount }}">
                            {{ $program->name }} (₱{{ number_format($program->min_amount) }} -
                            ₱{{ number_format($program->max_amount) }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Grace Period --}}
            <div class="form-group mt-3">
                <label for="with_grace">Grace Period:</label>
                <select name="with_grace" id="with_grace" class="form-control" required>
                    <option value="0">Without Grace Period</option>
                    <option value="1">With Grace Period</option>
                </select>
            </div>

            {{-- Loan Amount --}}
            <div class="form-group mt-3">
                <label for="loan_amount">Loan Amount:</label>
                <div class="input-group">
                    <input type="number" name="loan_amount" id="loan_amount" class="form-control"
                        placeholder="Enter Loan Amount" required>
                    <button type="button" class="btn btn-outline-primary" id="use_min">Use Min</button>
                    <button type="button" class="btn btn-outline-success" id="use_max">Use Max</button>
                </div>
                <small id="loan_range" class="form-text text-muted"></small>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Enroll</button>
        </form>
    </div>

    {{-- JavaScript --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const programSelect = document.getElementById("program_id");
            const loanInput = document.getElementById("loan_amount");
            const loanRange = document.getElementById("loan_range");
            const btnMin = document.getElementById("use_min");
            const btnMax = document.getElementById("use_max");

            let min = null, max = null;

            function updateRange() {
                const selected = programSelect.options[programSelect.selectedIndex];
                min = selected.getAttribute("data-min");
                max = selected.getAttribute("data-max");

                if (min && max) {
                    loanRange.innerText = `Allowed range: ₱${min} - ₱${max}`;
                    loanInput.min = min;
                    loanInput.max = max;
                } else {
                    loanRange.innerText = "";
                    loanInput.removeAttribute("min");
                    loanInput.removeAttribute("max");
                }

                loanInput.value = "";
            }

            btnMin.addEventListener("click", function () {
                if (min) loanInput.value = min;
            });

            btnMax.addEventListener("click", function () {
                if (max) loanInput.value = max;
            });

            programSelect.addEventListener("change", updateRange);

            updateRange();
        });
    </script>

    {{-- Optional: Add Select2 for typing/searchable cooperative --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#coop_id').select2({
                placeholder: '-- Select Cooperative --',
                allowClear: true
            });
        });
    </script>
@endsection