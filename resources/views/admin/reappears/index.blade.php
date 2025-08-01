@extends('layouts.admin')
@section('title', 'Reappear Admit Card')

@section('content')

<div class="container-fluid px-4 py-4">
    {{-- Header: Title + Add Button --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body">
            <div class="row align-items-end g-3">

                {{-- Column 1: Title --}}
                <div class="col-lg-8 col-md-12">
                    <div>
                        <h3 class="fw-bold text-primary mb-1">Reappear Admit Card Download</h3>
                          <p class="text-muted small mb-0">Reappear Admit Card Download</p>
                    </div>
                </div>

                {{-- Column 2: Add Button --}}
              

            </div>
        </div>
    </div>



    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- === Bulk Download === --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Download Admit Cards (By Filter)</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.admitcard.reappear') }}" target="_blank">
                @csrf
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Academic Session</label>
                        <select class="form-select" name="academic_session_id" required>
                            <option value="">-- Select --</option>
                            @foreach($academicSessions as $as)
                                <option value="{{ $as->id }}">{{ $as->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Institute</label>
                        <select class="form-select" name="institute_id" required>
                            <option value="">-- Select --</option>
                            @foreach($institutes as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Program</label>
                        <select name="program_id" class="form-select program-select" data-scope="bulk" required>
                            <option value="">-- Select --</option>
                            @foreach ($programs as $prog)
                                <option value="{{ $prog->id }}" data-structure="{{ $prog->structure }}">
                                    {{ $prog->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Semester / Year --}}
                    <div class="col-md-4 d-none" id="bulk-semester-wrapper">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester">
                            <option value="">-- Select Semester --</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-4 d-none" id="bulk-year-wrapper">
                        <label class="form-label">Year</label>
                        <select class="form-select" name="year">
                            <option value="">-- Select Year --</option>
                            @for ($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}">Year {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-12 text-end mt-3">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-download"></i> Download Admit Cards
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- === Individual Download === --}}
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Download Admit Card (By Roll Number)</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.admitcard.reappear.single') }}" target="_blank">
                @csrf
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Academic Session</label>
                        <select class="form-select" name="academic_session_id" required>
                            <option value="">-- Select --</option>
                            @foreach($academicSessions as $as)
                                <option value="{{ $as->id }}">{{ $as->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">NCHM Roll Number</label>
                        <input type="text" name="nchm_roll_number" class="form-control" required placeholder="Enter Roll No">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Program</label>
                        <select name="program_id" class="form-select program-select" data-scope="single" required>
                            <option value="">-- Select --</option>
                            @foreach ($programs as $prog)
                                <option value="{{ $prog->id }}" data-structure="{{ $prog->structure }}">
                                    {{ $prog->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Semester / Year --}}
                    <div class="col-md-4 d-none" id="single-semester-wrapper">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester">
                            <option value="">-- Select Semester --</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-4 d-none" id="single-year-wrapper">
                        <label class="form-label">Year</label>
                        <select class="form-select" name="year">
                            <option value="">-- Select Year --</option>
                            @for ($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}">Year {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-search"></i> Download Admit Card
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selects = document.querySelectorAll('.program-select');

    selects.forEach(select => {
        select.addEventListener('change', function () {
            const structure = this.selectedOptions[0]?.dataset.structure;
            const scope = this.dataset.scope;

            const semWrapper = document.getElementById(`${scope}-semester-wrapper`);
            const yearWrapper = document.getElementById(`${scope}-year-wrapper`);

            // Reset visibility
            semWrapper?.classList.add('d-none');
            yearWrapper?.classList.add('d-none');

            if (structure === 'semester') {
                semWrapper?.classList.remove('d-none');
            } else if (structure === 'yearly') {
                yearWrapper?.classList.remove('d-none');
            }
        });
    });
});
</script>
@endpush
