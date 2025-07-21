{{--  resources/views/admin/examination/regular/results.blade.php  --}}
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
        <strong>Compilation Errors:</strong><br>
        <ul class="mb-0">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

    {{-- ░░░ FILTER CARD ░░░ --}}
    <div class="card shadow-sm mb-4 animate__animated animate__fadeIn">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-secondary">Filter Options</h5>
        </div>

        <div class="card-body">
            {{-- ---------- SHOW (GET) ---------- --}}
            <form id="showForm" method="GET"
                  action="{{ route('admin.regular.exams.results', $session->id) }}">

                <div class="row g-3 align-items-end">
                    {{-- Session --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="session_id">Academic Session</label>
                        <select id="session_id" name="session_id" class="form-select" required>
                            <option value="">-- Select Session --</option>
                            @foreach ($sessions as $s)
                                <option  value="{{ $s->id }}"
                                         data-session-url="{{ route('admin.regular.exams.results', $s->id) }}"
                                         {{ $s->id == $session->id ? 'selected' : '' }}>
                                    {{ $s->year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Programme --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="program_id">Programme</label>
                        <select id="program_id" name="program_id" class="form-select" required>
                            <option value="">-- Select Programme --</option>
                            @foreach ($programs as $p)
                                <option value="{{ $p->id }}"
                                        {{ old('program_id', $programId ?? '') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Semester --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="semester">Semester</label>
                        <select id="semester" name="semester" class="form-select" required>
                            <option value="">-- Select --</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}"
                                        {{ old('semester', $semester ?? '') == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div><!-- /.row (inputs) -->

                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-eye"></i> Show
                        </button>
                    </div>
                </div>
            </form>

            {{-- ---------- COMPILE (POST) ---------- --}}
            <form id="compileForm" method="POST"
                  action="{{ route('admin.regular.exams.results.compile', $session->id) }}" class="mt-2">
                @csrf
                {{-- these will be set just‑in‑time by JS --}}
                <input type="hidden" name="program_id">
                <input type="hidden" name="semester">
                <input type="hidden" name="action"    value="compile">

                <div class="row">
                    <div class="col-md-12 ">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-graph-up"></i> Compile
                        </button>
                    </div>
                </div>
            </form>
        </div><!-- /.card‑body -->
    </div><!-- /.card -->

    {{-- ░░░ MARKS TABLE ░░░ --}}
    @isset($students)
        @if ($students->count())
            <div class="mb-3 text-end">
                <form method="GET" action="{{ route('admin.regular.exams.results.download', $session->id) }}">
                    <input type="hidden" name="program_id" value="{{ $programId }}">
                    <input type="hidden" name="semester"   value="{{ $semester  }}">
                    <button class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-excel"></i> Download Excel
                    </button>
                </form>
            </div>

            <div class="card shadow-sm animate__animated animate__fadeInUp">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-secondary">{{ $program->name }} – Semester {{ $semester }}</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered align-middle small table-hover">
                        <thead class="table-dark">
                        <tr>
                            <th rowspan="2">#</th>
                            <th rowspan="2">Roll&nbsp;No</th>
                            <th rowspan="2">Name</th>
                            @foreach ($courses as $c)
                                <th colspan="2" class="text-center">{{ $c->course_code }}</th>
                            @endforeach
                            <th rowspan="2" class="bg-dark text-center">Total</th>
                        </tr>
                        <tr>
                            @foreach ($courses as $c)
                                <th class="text-center text-muted">Int</th>
                                <th class="text-center text-muted">Ext</th>
                            @endforeach
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($students as $student)
                            @php $rowTotal = 0; @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $student->nchm_roll_number }}</td>
                                <td>{{ $student->name }}</td>
                                @foreach ($courses as $course)
                                    @php
                                        $mark = $index[$student->id][$course->id] ?? null;
                                        $int  = $mark->internal ?? 0;
                                        $ext  = $mark->external ?? 0;
                                        $rowTotal += $int + $ext;
                                    @endphp
                                    <td class="text-center">{{ $int }}</td>
                                    <td class="text-center">{{ $ext }}</td>
                                @endforeach
                                <td class="fw-bold text-center bg-light">{{ $rowTotal }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-warning">No marks found for the selected filters.</div>
        @endif
    @endisset
</div>

@endsection

@push('scripts')
<script>
/* ---- change session (full page reload) ---- */
document.getElementById('session_id').addEventListener('change', e => {
    const url = e.target.selectedOptions[0].dataset.sessionUrl;
    if (url) window.location.href = url;
});

/* ---- copy current selects → compile‑form hidden inputs ---- */
function syncToCompileForm () {
    const compile = document.getElementById('compileForm');
    compile.program_id.value = document.getElementById('program_id').value;
    compile.semester.value   = document.getElementById('semester').value;
}
['change','input'].forEach(evt => {
    document.getElementById('program_id').addEventListener(evt, syncToCompileForm);
    document.getElementById('semester').addEventListener(evt,   syncToCompileForm);
});
syncToCompileForm();      // run once on load
</script>
@endpush
