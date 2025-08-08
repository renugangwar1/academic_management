@extends('layouts.institute')

@section('title', 'Admit Cards')

@section('content')
<div class="container py-4">
    <h4 class="mb-4 text-primary">🎓 Manage Admit Cards</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- === Bulk Download === --}}
    <div class="card mb-4 shadow-sm border-0 rounded-4">
        <div class="card-header bg-light fw-semibold">Download Admit Cards (By Filter)</div>
        <div class="card-body">
            <form method="POST" action="{{ route('institute.admitcard.bulk') }}" target="_blank">
                @csrf
                <input type="hidden" name="session_id" value="{{ $session->id }}">
                <div class="row g-3">

                    {{-- Academic Session --}}
                    <div class="col-md-4">
                        <label class="form-label">Academic Session</label>
                        <select class="form-select" name="academic_session_id" required>
                            <option value="">-- Select --</option>
                            @foreach($academicSessions as $as)
                                <option value="{{ $as->id }}">{{ $as->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Program --}}
                    <div class="col-md-4">
                        <label class="form-label">Program</label>
                        <select name="program_id" class="form-select program-select" data-scope="bulk" required>
                            <option value="">-- Select --</option>
                            @foreach ($programs as $prog)
                                <option value="{{ $prog->id }}" data-structure="{{ $prog->structure }}">{{ $prog->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Semester --}}
<div class="col-md-4" id="bulk-semester-wrapper">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester">
                            <option value="">-- Select Semester --</option>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Year --}}
                    <div class="col-md-4 d-none" id="bulk-year-wrapper">
                        <label class="form-label">Year</label>
                        <select class="form-select" name="year">
                            <option value="">-- Select Year --</option>
                            @for($i = 1; $i <= 6; $i++)
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

    {{-- === Single Admit Card Download === --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-light fw-semibold">Download Admit Card (By Roll Number)</div>
        <div class="card-body">
            <form method="POST" action="{{ route('institute.admitcard.single') }}" target="_blank">
                @csrf
                <input type="hidden" name="session_id" value="{{ $session->id }}">
                <div class="row g-3">

                    {{-- Academic Session --}}
                    <div class="col-md-4">
                        <label class="form-label">Academic Session</label>
                        <select class="form-select" name="academic_session_id" required>
                            <option value="">-- Select --</option>
                            @foreach($academicSessions as $as)
                                <option value="{{ $as->id }}">{{ $as->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Roll Number --}}
                    <div class="col-md-4">
                        <label class="form-label">NCHM Roll Number</label>
                        <input type="text" name="nchm_roll_number" class="form-control" required placeholder="Enter Roll No">
                    </div>

                    {{-- Program --}}
                    <div class="col-md-4">
                        <label class="form-label">Program</label>
                        <select name="program_id" class="form-select program-select" data-scope="single" required>
                            <option value="">-- Select --</option>
                            @foreach ($programs as $prog)
                                <option value="{{ $prog->id }}" data-structure="{{ $prog->structure }}">{{ $prog->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Semester --}}
                    <div class="col-md-4 d-none" id="single-semester-wrapper">
                        <label class="form-label">Semester</label>
                        <select class="form-select" name="semester">
                            <option value="">-- Select Semester --</option>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Year --}}
                    <div class="col-md-4 d-none" id="single-year-wrapper">
                        <label class="form-label">Year</label>
                        <select class="form-select" name="year">
                            <option value="">-- Select Year --</option>
                            @for($i = 1; $i <= 6; $i++)
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

