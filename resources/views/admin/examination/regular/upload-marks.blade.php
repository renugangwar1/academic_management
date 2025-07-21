{{-- resources/views/admin/examination/regular/upload-marks.blade.php --}}
@extends('layouts.admin')
@section('title','Upload Marks')

@section('content')
@php
    $preview  = $previewData ?? [];
    $columns  = $columns     ?? [];
    $markType = $markType    ?? '';

       $programId = session('programId');   // or old('program_id')
    $semester  = session('semester');
@endphp

@include('admin.examination.partials.navbar')

<div class="container py-4">
    <h2 class="text-primary fw-bold mb-4">Upload Marks</h2>
{{-- Flash + Validation Errors --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Error:</strong> {{ $errors->first() }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


    {{-- ========== CARD 1: Upload Marks ========== --}}
    <div class="card border-primary mb-4">
        <div class="card-header bg-primary text-white">
            Upload Marks File
         
        </div>

        <div class="card-body">
            <form id="uploadForm"
                  action="{{ route('admin.regular.exams.upload-marks.file') }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                {{-- Dropdowns --}}
                <div class="row g-3 mb-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Academic Session</label>
                        <select name="session_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach ($academicSessions as $s)
                               <option value="{{ $s->id }}">
    {{ $s->year }}
</option>

                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Program</label>
                        <select name="program_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach ($programs as $prog)
                                <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select" required>
                            <option value="">-- Select --</option>
                            @for($i=1;$i<=8;$i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- Other Inputs --}}
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Mark Type</label>
                        <select name="mark_type" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="internal" @selected(old('mark_type') === 'internal')>Internal</option>
                            <option value="external" @selected(old('mark_type') === 'external')>External</option>
                            <option value="attendance" @selected(old('mark_type') === 'attendance')>Attendance</option>
                            <option value="all" @selected(old('mark_type') === 'all')>All</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Download Template</label>
                     <a id="download-template-btn" class="btn btn-outline-info w-100" target="_blank" href="#">
    <i class="bi bi-download"></i> Template
</a>

                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Upload Excel File</label>
                        <input type="file" name="marks_file" accept=".csv,.xls,.xlsx" class="form-control" required>
                    </div>

                    <div class="col-md-3 align-self-end">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-upload"></i> Upload Marks
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== CARD 2: Download Uploaded Marks ========== --}}
    <div class="card border-secondary mb-4">
        <div class="card-header bg-secondary text-white">
            Download Uploaded Marks
        
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.regular.exams.download-uploaded-marks') }}">
                @csrf

                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Academic Session</label>
                        <select name="session_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach ($academicSessions as $s)
                              <option value="{{ $s->id }}">
    {{ $s->year }}
</option>

                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Program</label>
                        <select name="program_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach ($programs as $prog)
                                <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Semester</label>
                       <select name="semester" class="form-select" required>
                            <option value="">-- Select --</option>
                            @for($i=1;$i<=8;$i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Mark Type</label>
                        <select name="mark_type" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="internal">Internal</option>
                            <option value="external">External</option>
                            <option value="attendance">Attendance</option>
                            <option value="all">All</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-success w-100">
                            <i class="bi bi-download"></i> Download
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== CARD 3: Compile Internal Marks ========== --}}
    <div class="card border-dark">
        <div class="card-header bg-dark text-white">
            Compile Internal Marks
            
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.regular.exams.compile.internal') }}">
                @csrf

                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Academic Session</label>
                        <select name="session_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach ($academicSessions as $s)
                              <option value="{{ $s->id }}">
    {{ $s->year }}
</option>

                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Program</label>
                        <select name="program_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach ($programs as $prog)
                                <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select" required>
                            <option value="">-- Select --</option>
                            @for($i=1;$i<=8;$i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-gear-wide-connected"></i> Compile
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== Preview Table ========== --}}
    @if(count($preview))
    <form method="POST"
      action="{{ route('admin.regular.exams.finalize.marks', $session->id) }}">
    @csrf
    <input type="hidden" name="mark_type" value="{{ $markType }}">
    <input type="hidden" name="session_id" value="{{ $session->id }}">
   <input type="hidden" name="program_id" value="{{ old('program_id', $programId) }}">
<input type="hidden" name="semester"   value="{{ old('semester',   $semester) }}">



            @foreach($columns as $c)
                <input type="hidden" name="columns[]" value="{{ $c }}">
            @endforeach

            <div class="card border-warning mt-5">
                <div class="card-header bg-warning text-dark">
                    Preview Uploaded Marks
                  
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Roll No</th>
                                    <th>Name</th>
                                    @foreach($columns as $c)
                                        <th>{{ $c }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preview as $i => $row)
                                    <tr>
                                        <td>
                                            {{ $row['nchm_roll_number'] }}
                                            <input type="hidden" name="preview_data[{{ $i }}][nchm_roll_number]" value="{{ $row['nchm_roll_number'] }}">
                                        </td>
                                        <td>
                                            {{ $row['name'] }}
                                            <input type="hidden" name="preview_data[{{ $i }}][name]" value="{{ $row['name'] }}">
                                        </td>
                                        @foreach($columns as $c)
                                            <td>
                                                {{ $row[$c] ?? '-' }}
                                                <input type="hidden" name="preview_data[{{ $i }}][{{ $c }}]" value="{{ $row[$c] ?? '' }}">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end">
                        <button class="btn btn-success px-4">
                            <i class="bi bi-check-circle"></i> Submit Final Marks
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const templateBtn = document.getElementById('download-template-btn');
        const sessionSelect = document.querySelector('select[name="session_id"]');
        const programSelect = document.querySelector('select[name="program_id"]');
        const semesterSelect = document.querySelector('select[name="semester"]');
        const markTypeSelect = document.querySelector('select[name="mark_type"]');

        function updateDownloadLink() {
            const sessionId = sessionSelect.value;
            const programId = programSelect.value;
            const semester = semesterSelect.value;
            const markType = markTypeSelect.value;

            if (sessionId && programId && semester && markType) {
                const url = `{{ route('admin.regular.exams.template') }}?session_id=${sessionId}&program_id=${programId}&semester=${semester}&mark_type=${markType}`;
                templateBtn.href = url;
                templateBtn.classList.remove('disabled');
            } else {
                templateBtn.href = '#';
                templateBtn.classList.add('disabled');
            }
        }

        sessionSelect.addEventListener('change', updateDownloadLink);
        programSelect.addEventListener('change', updateDownloadLink);
        semesterSelect.addEventListener('change', updateDownloadLink);
        markTypeSelect.addEventListener('change', updateDownloadLink);
    });
</script>
@endpush
