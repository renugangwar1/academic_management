@extends('layouts.admin')
@section('title', 'Reappear Admit Cards')

@section('content')

<div class="container py-4">
    <h4 class="mb-4 text-danger fw-bold">
        <i class="bi bi-arrow-repeat me-1"></i> Reappear Admit Card Download
    </h4>

    {{-- Flash Messages --}}
    @foreach (['success','error'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg == 'success' ? 'success' : 'danger' }}">
                {{ session($msg) }}
            </div>
        @endif
    @endforeach

    {{-- ========== Bulk Download ========== --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light"><h5 class="mb-0">By Filter</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.admitcard.reappear') }}" target="_blank">
                @csrf
                <div class="row g-3">
                    {{-- Institute --}}
                    <div class="col-md-4">
                        <label class="form-label">Institute</label>
                        <select class="form-select" name="institute_id" required>
                            <option value="">-- Select --</option>
                            @foreach ($institutes as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Program --}}
                    <div class="col-md-4">
                        <label class="form-label">Program</label>
                        <select class="form-select" name="program_id" id="program-filter" required>
                            <option value="">-- Select --</option>
                            @foreach ($programs as $prog)
                                <option value="{{ $prog->id }}"
                                        data-structure="{{ $prog->structure }}">
                                    {{ $prog->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Semester / Year (toggles via JS) --}}
                    <div class="col-md-4" id="sem-wrapper"  style="display:none;">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester">
                            <option value="">-- Select Semester --</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-4" id="year-wrapper" style="display:none;">
                        <label class="form-label">Year</label>
                        <select class="form-select" name="year">
                            <option value="">-- Select Year --</option>
                            @for ($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}">Year {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-download"></i> Download Admit Cards
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== Single Download ========== --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light"><h5 class="mb-0">By Roll&nbsp;Number</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.admitcard.reappear.single') }}" target="_blank">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">NCHM Roll Number</label>
                        <input type="text" name="nchm_roll_number" class="form-control" required>
                    </div>
                    <div class="col-md-6 align-self-end">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-download"></i> Download Admit Card
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
    const program =   document.getElementById('program-filter');
    const semWrap  =  document.getElementById('sem-wrapper');
    const yearWrap =  document.getElementById('year-wrapper');

    const toggle = () => {
        const opt = program.options[program.selectedIndex];
        const struct = opt.getAttribute('data-structure');
        if (struct === 'semester') {
            semWrap.style.display  = 'block';
            yearWrap.style.display = 'none';
            yearWrap.querySelector('select').value = '';
        } else if (struct === 'yearly') {
            yearWrap.style.display = 'block';
            semWrap.style.display  = 'none';
            semWrap.querySelector('select').value = '';
        } else {
            semWrap.style.display  = 'none';
            yearWrap.style.display = 'none';
        }
    };
    program.addEventListener('change', toggle); toggle();
});
</script>
@endpush
