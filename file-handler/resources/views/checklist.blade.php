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

        {{-- Checklist Uploads --}}
        @foreach($checklistItems as $item)
            <div class="card my-3 p-3">
                <h5>{{ $item->checklist->name }}</h5>
                <form action="{{ route('checklist.upload', $cooperative->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="program_checklist_id" value="{{ $item->id }}">
                    <input type="file" name="file" required>
                    <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                </form>

                @php
                    $uploads = $item->uploads->where('coop_program_id', $cooperative->id);
                @endphp

                @if($uploads->isNotEmpty())
                    <div class="mt-2">
                        @foreach($uploads as $upload)
                            <a href="{{ route('checklist.download', $upload->id) }}" class="btn btn-success btn-sm d-block mb-1">
                                Download - {{ $upload->file_name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Finalize Loan --}}
        @php
            $total = $checklistItems->count();
            $uploaded = $checklistItems->filter(fn($i) => $i->uploads->isNotEmpty())->count();
        @endphp

        @if($total)
            <div class="card my-4 p-3 border-success">
                <h4>Finalize Loan Details</h4>
                <div class="form-group mt-3">
                    <label for="loan_amount">Loan Amount:</label>
                    <div class="input-group">
                        <input type="number" id="loan_amount" class="form-control" placeholder="Enter Loan Amount">
                        <button type="button" class="btn btn-outline-primary" id="use_min">Use Min</button>
                        <button type="button" class="btn btn-outline-success" id="use_max">Use Max</button>
                    </div>
                    <small id="loan_range" class="form-text text-muted"></small>
                </div>
                <div class="form-group mt-3">
                    <label for="start_date">Start Date of Loan:</label>
                    <input type="date" id="start_date" class="form-control">
                </div>
                <div class="col-md-4 mt-2">
                    <label for="with_grace">Grace Period (months)</label>
                    <input type="number" min="0" class="form-control" id="with_grace">
                </div>

                <div class="form-check mt-3">
                    <input type="checkbox" class="form-check-input" id="consent">
                    <label class="form-check-label" for="consent">
                        I certify that all of my uploaded files are correct.
                    </label>
                </div>

                {{-- Finalize Loan Button opens MOA Modal --}}
                <button type="button" class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#moaModal">
                    Finalize Loan
                </button>
            </div>

            <!-- MOA Modal -->
            <div class="modal fade" id="moaModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('program.finalize', $cooperative->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Upload MOA Before Finalizing</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <!-- Hidden Loan Fields -->
                                <input type="hidden" name="loan_ammount" id="modal_loan_ammount">
                                <input type="hidden" name="with_grace" id="modal_with_grace">
                                <input type="hidden" name="start_date" id="modal_start_date">
                                <input type="hidden" name="consent" id="modal_consent">

                                <!-- MOA Upload -->
                                <div class="mb-3">
                                    <label>Upload MOA (PDF only)</label>
                                    <input type="file" name="moa_file" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Date Signed</label>
                                    <input type="date" name="date_signed" class="form-control">
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Confirm & Finalize</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- JS for min/max buttons and modal field sync --}}
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const loanInput = document.getElementById("loan_amount");
                    const loanRange = document.getElementById("loan_range");
                    const btnMin = document.getElementById("use_min");
                    const btnMax = document.getElementById("use_max");

                    let min = {{ $cooperative->program->min_amount }};
                    let max = {{ $cooperative->program->max_amount }};

                    loanRange.innerText = `Allowed range: ₱${min.toLocaleString()} - ₱${max.toLocaleString()}`;
                    loanInput.min = min;
                    loanInput.max = max;

                    btnMin.addEventListener("click", () => loanInput.value = min);
                    btnMax.addEventListener("click", () => loanInput.value = max);

                    // Sync modal hidden fields when modal opens
                    const moaModal = document.getElementById('moaModal');
                    moaModal.addEventListener('show.bs.modal', function () {
                        document.getElementById('modal_loan_ammount').value = loanInput.value;
                        document.getElementById('modal_start_date').value = document.getElementById('start_date').value;
                        document.getElementById('modal_with_grace').value = document.getElementById('with_grace').value;
                        document.getElementById('modal_consent').value = document.getElementById('consent').checked ? 1 : 0;
                    });
                });
            </script>
        @endif
    </div>
@endsection