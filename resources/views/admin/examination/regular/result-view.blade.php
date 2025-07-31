@extends('layouts.admin')
@section('title', "Result – {{ $programName ?? 'Program' }} (Sem {{ $semester }})")

@section('content')
<div class="container py-4">
    <h4 class="mb-4 text-primary">Result Download</h4>

    {{-- === Bulk Result Download (With Filters) === --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Download Result (By Filter)</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.exams.results.download-bulk') }}" target="_blank">
                @csrf
                <div class="row g-3">
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
                        <select class="form-select" name="program_id" required>
                            <option value="">-- Select --</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester">
                            <option value="">-- Select Semester --</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                  


                    <div class="col-md-4">
                        <label class="form-label">Academic Session</label>
                                      <select class="form-select" name="academic_session_id" required>
    <option value="">-- Select --</option>
    @foreach($academicSessions as $academicSession)
        <option value="{{ $academicSession->id }}">
    {{ $academicSession->display_name }}
</option>

    @endforeach
</select>
         
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Format</label>
                        <select class="form-select" name="format" required>
                            <option value="html">PDF</option>
                        </select>
                    </div>
                    

                    <div class="col-md-12 text-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-download"></i> Download Result (Bulk)
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- === Roll No Wise Result Download === --}}
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Download Result (By Roll Number)</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.exams.results.download-roll') }}" target="_blank">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">NCHM Roll Number</label>
                        <input type="text" name="nchm_roll_number" class="form-control" required placeholder="Enter Roll No">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Program</label>
                        <select class="form-select" name="program_id" required>
                            <option value="">-- Select --</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester">
                            <option value="">-- Select Semester --</option>
                            @for ($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>


                  

                    <div class="col-md-4">
                        <label class="form-label">Academic Session</label>
                        <select class="form-select" name="academic_session_id" required>
                            <option value="">-- Select --</option>
                            @foreach($academicSessions as $academicSession)
                                <option value="{{ $academicSession->id }}">{{ $academicSession->year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 align-self-end">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-search"></i> Download Result
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
