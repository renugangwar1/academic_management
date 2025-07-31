@extends('layouts.admin')
@section('title', 'Process Results – Regular')

@section('content')
@include('admin.examination.partials.navbar')

<div class="container py-4">
    <h3 class="mb-4 text-primary fw-bold">
        <i class="bi bi-clipboard-data me-2"></i> Process Results – Regular
    </h3>

    {{-- Flash messages --}}
    @foreach (['success', 'error'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type === 'success' ? 'success' : 'danger' }}">
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Compilation Errors:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ░░░ FILTER & COMPILE ░░░ --}}





    
    <!-- ///////////////////////////// -->
    <div class="card shadow-sm mb-4 animate__animated animate__fadeIn">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-secondary">Filter & Compile Options</h5>
        </div>
        <div class="card-body">
            <form id="showForm" method="GET" action="{{ route('admin.regular.exams.results') }}">
                <div class="row g-3 align-items-end">
                    {{-- Academic Session --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Academic Session</label>
                    <select name="academic_session_id" class="form-select" required>
    <option value="">-- Select --</option>
    @foreach($academicSessions as $academicSession)
        <option value="{{ $academicSession->id }}" 
            {{ request('academic_session_id') == $academicSession->id ? 'selected' : '' }}>
            {{ $academicSession->display_name }}
        </option>
    @endforeach
</select>

                    </div>

                    {{-- Programme --}}
                   <div class="col-md-3">
                        <label class="form-label">Program</label>
                     <select id="program_id" name="program_id" class="form-select" required>
    <option value="">-- Select --</option>
    @foreach ($programs as $prog)
        <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>
            {{ $prog->name }}
        </option>
    @endforeach
</select>

                    </div>

                    {{-- Semester --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Semester</label>
                      <select id="semester" name="semester" class="form-select" required>
    <option value="">-- Select --</option>
    @for ($i = 1; $i <= 10; $i++)
        <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>
            Semester {{ $i }}
        </option>
    @endfor
</select>

                    </div>
                </div>


                <div class="row mt-3 g-2">
                    {{-- Show Results Button --}}
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-eye"></i> Show Results
                        </button>
                    </div>
            </form>

                    {{-- Compile Results Form --}}
                    <div class="col-md-6">
                        <form id="compileForm" method="POST"
                              action="{{ route('admin.regular.exams.results.compile', ['session' => request('academic_session_id') ?? 0]) }}">
                            @csrf
                            <input type="hidden" name="academic_session_id">
                            <input type="hidden" name="program_id">
                            <input type="hidden" name="semester">
                            <input type="hidden" name="action" value="compile">

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-gear-wide-connected"></i> Compile Results
                            </button>
                        </form>
                    </div>
                </div>
        </div>
    </div>

    {{-- Results Table --}}
    @if(isset($students) && $students->count())
    <!-- ////////////// -->

          <div class="mb-3 text-end">
            <form method="GET" action="{{ route('admin.regular.exams.results.download') }}">
                <input type="hidden" name="academic_session_id" value="{{ request('academic_session_id') }}">
                <input type="hidden" name="program_id" value="{{ request('program_id') }}">
                <input type="hidden" name="semester" value="{{ request('semester') }}">
                <button class="btn btn-outline-primary">
                    <i class="bi bi-file-earmark-excel"></i> Download Excel
                </button>
            </form>
        </div>
        <!-- Download External Result Excel -->

    <!-- ///////////// -->
        <div class="card shadow-sm animate__animated animate__fadeIn">
            <div class="card-header bg-light">
                <h5 class="mb-0 text-secondary">Results Table</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2">#</th>
                            <th rowspan="2">Roll No</th>
                            <th rowspan="2">Name</th>
                            <th rowspan="2">Program</th>
                            <th rowspan="2">Semester</th>
                            @foreach($courses as $course)
                                <th colspan="2" class="text-center">{{ $course->course_code }}</th>
                            @endforeach
                            <th rowspan="2" class="text-center">Total</th>
                        </tr>
                        <tr>
                            @foreach($courses as $course)
                                <th class="text-center">Int</th>
                                <th class="text-center">Ext</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $student->nchm_roll_number }}</td>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->program->name ?? '-' }}</td>
                                <td>{{ $student->semester ?? '-' }}</td>

                                @php $totalMarks = 0; @endphp

                                @foreach($courses as $course)
                                    @php
                                        $mark = $student->marks->firstWhere('course_id', $course->id);
                                        $int = $mark->internal ?? 0;
                                        $ext = $mark->external ?? 0;
                                        $totalMarks += $int + $ext;
                                    @endphp
                                    <td class="text-center">{{ $int }}</td>
                                    <td class="text-center">{{ $ext }}</td>
                                @endforeach

                                <td class="text-center fw-bold text-primary">{{ $totalMarks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif(request()->filled(['academic_session_id', 'program_id', 'semester']))
        <div class="alert alert-warning">
            No results found for the selected filters.
        </div>
    @endif
</div>

{{-- Sync Filter Values into Compile Form --}}
@push('scripts')
<script>
    function syncToCompileForm() {
        const compileForm = document.querySelector('#compileForm');
        if (!compileForm) return;

        compileForm.academic_session_id.value = document.querySelector('[name="academic_session_id"]').value;
        compileForm.program_id.value = document.querySelector('#program_id').value;
        compileForm.semester.value = document.querySelector('#semester').value;
    }

    ['change', 'input'].forEach(eventType => {
        document.querySelector('[name="academic_session_id"]').addEventListener(eventType, syncToCompileForm);
        document.querySelector('#program_id').addEventListener(eventType, syncToCompileForm);
        document.querySelector('#semester').addEventListener(eventType, syncToCompileForm);
    });

    syncToCompileForm(); // Run on initial load


  
    // Sync compile form with show form filters
    function syncToCompileForm() {
        const compileForm = document.querySelector('#compileForm');
        if (!compileForm) return;

        compileForm.academic_session_id.value = document.querySelector('[name="academic_session_id"]').value;
        compileForm.program_id.value = document.querySelector('#program_id').value;
        compileForm.semester.value = document.querySelector('#semester').value;
    }

    ['change', 'input'].forEach(evt => {
        document.querySelector('[name="academic_session_id"]').addEventListener(evt, syncToCompileForm);
        document.querySelector('#program_id').addEventListener(evt, syncToCompileForm);
        document.querySelector('#semester').addEventListener(evt, syncToCompileForm);
    });

    syncToCompileForm(); // Run on load

    // 🔁 Dynamic filtering: Fetch updated programs and semester info
    document.querySelector('[name="academic_session_id"]').addEventListener('change', function () {
        let sessionId = this.value;
        if (!sessionId) return;

        fetch(`/admin/regular/ajax/fetch-courses?academic_session_id=${sessionId}`)
            .then(res => res.json())
            .then(data => {
                if (data.semester) {
                    // Optional: pre-select semester or use in logic
                    console.log('Fetched semester info:', data.semester);
                }

                // Example: Show notification or update other parts dynamically
                // You can use this data to repopulate other dropdowns if needed
            })
            .catch(err => console.error('Error fetching course info:', err));
    });


</script>
@endpush
@endsection
