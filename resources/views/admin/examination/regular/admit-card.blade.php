@extends('layouts.admin')
@section('title', 'Admit Card')

@section('content')
@include('admin.examination.partials.navbar')

<div class="container py-4">
    <h4 class="mb-4 text-primary">Admit Card Download ({{ ucfirst($session->type) }})</h4>

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
            <form method="POST" action="{{ route('admin.admitcard.bulk') }}" target="_blank">
                @csrf
                <input type="hidden" name="session_id" value="{{ $session->id }}">
                <div class="row g-3">
                    
                    <div class="col-md-4">
                        <label class="form-label">Academic Session</label>
                        <select class="form-select" name="academic_session_id" required>
                            <option value="">-- Select --</option>
                            @foreach($academicSessions as $academicSession)
                                <option value="{{ $academicSession->id }}">{{ $academicSession->year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Institute</label>
                        <select class="form-select" name="institute_id" required>
                            <option value="">-- Select --</option>
                            @foreach($institutes as $institute)
                                <option value="{{ $institute->id }}">{{ $institute->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Program</label>
                        <select class="form-select" name="program_id" id="program-select" required>
                            <option value="">-- Select --</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" data-structure="{{ $program->structure }}">
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4" id="semester-wrapper" style="display: none;">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester">
                            <option value="">-- Select Semester --</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-4" id="year-wrapper" style="display: none;">
                        <label class="form-label">Year</label>
                        <select class="form-select" name="year">
                            <option value="">-- Select Year --</option>
                            @for ($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}">Year {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-12 text-end mt-3">
                        <button type="submit" class="btn btn-primary">
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
            <form method="POST" action="{{ route('admin.admitcard.single') }}" target="_blank">
                @csrf
                <input type="hidden" name="session_id" value="{{ $session->id }}">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Academic Session</label>
                        <select class="form-select" name="academic_session_id" required>
                            <option value="">-- Select --</option>
                            @foreach($academicSessions as $academicSession)
                                <option value="{{ $academicSession->id }}">{{ $academicSession->year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">NCHM Roll Number</label>
                        <input type="text" name="nchm_roll_number" class="form-control" required placeholder="Enter Roll No">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select" required>
                            <option value="">-- Select Semester --</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
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
document.addEventListener('DOMContentLoaded', function () {
    const programSelect = document.getElementById('program-select');
    const semesterWrapper = document.getElementById('semester-wrapper');
    const yearWrapper = document.getElementById('year-wrapper');

    function toggleStructureDropdown() {
        const selected = programSelect.options[programSelect.selectedIndex];
        const structure = selected.getAttribute('data-structure');

        if (structure === 'semester') {
            semesterWrapper.style.display = 'block';
            yearWrapper.style.display = 'none';
        } else if (structure === 'yearly') {
            yearWrapper.style.display = 'block';
            semesterWrapper.style.display = 'none';
        } else {
            semesterWrapper.style.display = 'none';
            yearWrapper.style.display = 'none';
        }
    }

    programSelect.addEventListener('change', toggleStructureDropdown);
    toggleStructureDropdown(); // Trigger on page load
});
</script>
@endpush
