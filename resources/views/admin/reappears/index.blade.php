@extends('layouts.admin')
@section('title', 'Reappear Admit Card')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="card shadow-sm border-0 mb-4 rounded-4">
        <div class="card-body">
            <div class="row align-items-end g-3">
                <div class="col-lg-8 col-md-12">
                    <h3 class="fw-bold text-primary mb-1">Reappear Admit Card Download</h3>
                    <p class="text-muted small mb-0">Reappear Admit Card Download</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Reappear Admit Card Apply</h5>
        </div>
        <div class="card-body">
            {{-- ONE single form used for fetching list --}}
            <form id="fetch-reappear-form" method="POST" action="{{ route('admin.reappear.fetch') }}">
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
                        <button type="button" id="fetchReappearBtn" class="btn btn-warning">
                            <i class="bi bi-search"></i> Fetch Reappear List
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- === Download Form (Bulk Admit Cards PDF) === --}}
    <form method="POST" action="{{ route('admin.admitcard.reappear') }}" target="_blank">
        @csrf
        <input type="hidden" name="academic_session_id" value="">
        <input type="hidden" name="institute_id" value="">
        <input type="hidden" name="program_id" value="">
        <input type="hidden" name="semester" value="">
        <input type="hidden" name="year" value="">
    </form>

    {{-- === Reappear Students List Table === --}}
    <div class="card mt-4 d-none" id="reappear-students-card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Reappear Students List</h5>
        </div>
        <div class="card-body">
          <form id="gic-form" method="POST" action="{{ route('admin.admitcard.reappear.gic.store') }}">

                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                 <th>
            <input type="checkbox" id="select-all-students">
        </th>
                                 <th>S.No</th>
                                <th>Roll Number</th>
                                <th>Name</th>
                                <th>Subject Code</th>
                                <th>Subject Name</th>
                                <th>Reappear Type</th>
                                <th>Internal  Marks</th>
                            </tr>
                        </thead>
                        <tbody id="reappear-student-body">
                            {{-- Rows populated by JS --}}
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Save Marks</button>
                </div>
            </form>
        </div>
    </div>

<!-- //////////////////////////////////////////// -->


{{-- === Bulk Download Admit Cards PDF === --}}
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Download Bulk Admit Cards</h5>
    </div>
    <div class="card-body">
        <form id="bulk-download-form" method="POST" action="{{ route('admin.admitcard.reappear') }}" target="_blank">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Academic Session</label>
                    <select class="form-select" name="academic_session_id" id="bulk-academic-session-id" required>
                        <option value="">-- Select --</option>
                        @foreach($academicSessions as $as)
                            <option value="{{ $as->id }}">{{ $as->display_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Institute</label>
                    <select class="form-select" name="institute_id" id="bulk-institute-id" required>
                        <option value="">-- Select --</option>
                        @foreach($institutes as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Program</label>
                    <select name="program_id" class="form-select program-select" data-scope="bulk-download" id="bulk-program-id" required>
                        <option value="">-- Select --</option>
                        @foreach ($programs as $prog)
                            <option value="{{ $prog->id }}" data-structure="{{ $prog->structure }}">
                                {{ $prog->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-none" id="bulk-download-semester-wrapper">
                    <label class="form-label">Semester</label>
                    <select class="form-select" name="semester" id="bulk-semester">
                        <option value="">-- Select Semester --</option>
                        @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-4 d-none" id="bulk-download-year-wrapper">
                    <label class="form-label">Year</label>
                    <select class="form-select" name="year" id="bulk-year">
                        <option value="">-- Select Year --</option>
                        @for ($i = 1; $i <= 6; $i++)
                            <option value="{{ $i }}">Year {{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-12 text-end mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-download"></i> Download Bulk Admit Cards
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
















<!-- /////////////////////////////////////////////////////// -->
    {{-- === Individual Roll Admit Card Download === --}}
    <div class="card mt-4">
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

    // Program structure toggle
    document.querySelectorAll('.program-select').forEach(select => {
        select.addEventListener('change', function () {
            const structure = this.selectedOptions[0]?.dataset.structure;
            const scope = this.dataset.scope;

            document.getElementById(`${scope}-semester-wrapper`)?.classList.add('d-none');
            document.getElementById(`${scope}-year-wrapper`)?.classList.add('d-none');

            if (structure === 'semester') {
                document.getElementById(`${scope}-semester-wrapper`)?.classList.remove('d-none');
            } else if (structure === 'yearly') {
                document.getElementById(`${scope}-year-wrapper`)?.classList.remove('d-none');
            }
        });
    });

    // Fetch reappear students
    const fetchBtn = document.getElementById('fetchReappearBtn');
    fetchBtn?.addEventListener('click', () => {
        const form = document.getElementById('fetch-reappear-form');
        const formData = new FormData(form);

        const academicSessionId = formData.get('academic_session_id');
        const programId = formData.get('program_id');
        const semester = formData.get('semester');
        const year = formData.get('year');
        const instituteId = formData.get('institute_id');

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': formData.get('_token'),
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('reappear-student-body');
            const card = document.getElementById('reappear-students-card');
            tbody.innerHTML = '';

            if (Array.isArray(data) && data.length > 0) {
                data.forEach((item, index) => {
                    const key = `${item.student_id}_${item.course_code}`;
                    const alreadyStored = item.already_stored === true;
                    const maxMarks = item.internal_max ?? 100;

                    const row = document.createElement('tr');
                    row.setAttribute('data-max-marks', maxMarks);

                    row.innerHTML = `
                        <td>
                            <input type="checkbox" name="students[${key}][selected]" class="student-checkbox" value="1" ${alreadyStored ? 'disabled' : ''}>
                        </td>
                        <td>${index + 1}</td>
                        <td>
                            ${item.roll_number}
                            <input type="hidden" name="students[${key}][student_id]" value="${item.student_id}">
                            <input type="hidden" name="students[${key}][roll_number]" value="${item.roll_number}">
                        </td>
                        <td>
                            ${item.student_name}
                            <input type="hidden" name="students[${key}][student_name]" value="${item.student_name}">
                        </td>
                        <td>
                            ${item.course_code}
                            <input type="hidden" name="students[${key}][course_code]" value="${item.course_code}">
                            <input type="hidden" name="students[${key}][course_id]" value="${item.course_id}">
                        </td>
                        <td>
                            ${item.course_name}
                            <input type="hidden" name="students[${key}][course_name]" value="${item.course_name}">
                        </td>
                        <td>
                            ${item.reappear_type}
                            <input type="hidden" name="students[${key}][reappear_type]" value="${item.reappear_type}">
                            <input type="hidden" name="students[${key}][academic_session_id]" value="${academicSessionId}">
                            <input type="hidden" name="students[${key}][program_id]" value="${programId}">
                            <input type="hidden" name="students[${key}][semester]" value="${semester}">
                            <input type="hidden" name="students[${key}][year]" value="${year}">
                            <input type="hidden" name="students[${key}][institute_id]" value="${instituteId}">
                        </td>
                        <td>
                            <input type="number" 
                                name="students[${key}][gic_marks]" 
                                class="form-control gic-input gic-marks" 
                                placeholder="Enter Marks" 
                                max="${maxMarks}" 
                                min="0" 
                                step="0.01"
                                ${alreadyStored ? 'disabled' : ''}>
                            <div class="text-danger mark-error" style="font-size: 12px;"></div>
                            ${alreadyStored ? '<small class="text-success">Already submitted</small>' : ''}
                        </td>
                    `;

                    if (alreadyStored) {
                        row.classList.add('table-success');
                    }

                    tbody.appendChild(row);
                });

                card.classList.remove('d-none');
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted">No reappear students found.</td>
                    </tr>
                `;
                card.classList.remove('d-none');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error fetching reappear list.');
        });
    });

    // Validation before submitting GIC marks
    const gicForm = document.getElementById('gic-form');
    if (gicForm) {
        gicForm.addEventListener('submit', function (e) {
            let isValid = true;

            gicForm.querySelectorAll('.mark-error').forEach(el => el.textContent = '');
            gicForm.querySelectorAll('.gic-marks').forEach(el => el.classList.remove('is-invalid'));

            const checkedRows = gicForm.querySelectorAll('.student-checkbox:checked');

            if (checkedRows.length === 0) {
                e.preventDefault();
                alert("Please select at least one student.");
                return;
            }

            checkedRows.forEach(cb => {
                const row = cb.closest('tr');
                const marksInput = row.querySelector('.gic-marks');
                const errorDiv = row.querySelector('.mark-error');

                const maxMarks = parseFloat(row.getAttribute('data-max-marks')) || 100;
                const value = marksInput.value.trim();
                const numericVal = parseFloat(value);

                if (!value) {
                    isValid = false;
                    errorDiv.textContent = "Marks required";
                    marksInput.classList.add('is-invalid');
                } else if (isNaN(numericVal)) {
                    isValid = false;
                    errorDiv.textContent = "Enter valid number";
                    marksInput.classList.add('is-invalid');
                } else if (numericVal < 0) {
                    isValid = false;
                    errorDiv.textContent = "Marks cannot be negative";
                    marksInput.classList.add('is-invalid');
                } else if (numericVal > maxMarks) {
                    isValid = false;
                    errorDiv.textContent = `Max ${maxMarks} allowed`;
                    marksInput.classList.add('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                document.getElementById('reappear-students-card')?.classList.remove('d-none');
                alert("Please fix the errors before submitting.");
            }
        });
    }

    // Select All functionality
    document.getElementById('select-all-students')?.addEventListener('change', function () {
        document.querySelectorAll('.student-checkbox:not(:disabled)').forEach(cb => {
            cb.checked = this.checked;
        });
    });

});


// Already present, but confirm it includes 'bulk-download' as a scope
document.querySelectorAll('.program-select').forEach(select => {
    select.addEventListener('change', function () {
        const structure = this.selectedOptions[0]?.dataset.structure;
        const scope = this.dataset.scope;

        document.getElementById(`${scope}-semester-wrapper`)?.classList.add('d-none');
        document.getElementById(`${scope}-year-wrapper`)?.classList.add('d-none');

        if (structure === 'semester') {
            document.getElementById(`${scope}-semester-wrapper`)?.classList.remove('d-none');
        } else if (structure === 'yearly') {
            document.getElementById(`${scope}-year-wrapper`)?.classList.remove('d-none');
        }
    });
});

</script>
@endpush

<style>
.is-invalid {
    border-color: #dc3545 !important;
}
.invalid-feedback {
    color: #dc3545;
    font-size: 0.875em;
}
.table-success {
    opacity: 0.75;
    background-color: #d4edda !important;
}
</style>
