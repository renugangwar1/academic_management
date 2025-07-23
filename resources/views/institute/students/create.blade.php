@extends('layouts.institute')

@section('title', 'Add Student')

@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold text-success display-5"> Add New Student</h2>
      
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success mx-auto text-center" style="max-width: 600px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mx-auto text-center" style="max-width: 600px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger mx-auto text-center" style="max-width: 600px;">
            <ul class="mb-0 list-unstyled">
                @foreach($errors->all() as $error)
                    <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

   {{-- Import Section --}}
<div class="mx-auto" style="max-width: 900px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-info text-white text-center py-3">
            <h5 class="mb-0">📥 Import Students</h5>
        </div>
        <div class="card-body p-4">
            <p class="text-center fs-6 mb-3">
                Please select a program and upload your <strong>Excel file</strong> as per the template.
            </p>

            {{-- Program Dropdown --}}
            <div class="mb-3">
                <label for="programSelect" class="form-label fw-semibold">🎓 Select Program <span class="text-danger">*</span></label>
                <select id="programSelect" class="form-select shadow-sm" required>
                    <option value="">-- Choose a program --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Template Download --}}
            <div class="text-center mb-3">
                <a id="templateDownloadBtn"
                   href="#"
                   class="btn btn-outline-primary px-4 py-2 disabled"
                   target="_blank"
                   data-base-url="{{ route('institute.students.downloadTemplate') }}">
                    📥 Download Excel Template
                </a>
            </div>

            {{-- Upload Form --}}
            <form action="{{ route('institute.students.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="student_excel" class="form-label fw-semibold">📄 Upload Excel File <span class="text-danger">*</span></label>
                    <input type="file" name="student_excel" id="student_excel" class="form-control shadow-sm" required>
                </div>
                <button type="submit" class="btn btn-success w-100 py-2 fs-6">
                    📤 Upload Students
                </button>
            </form>
        </div>
    </div>
</div>


 {{-- Uploaded Files --}}
@if($uploads->count())
    <div class="mx-auto mt-4" style="max-width: 900px;">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white text-center py-3">
                <h5 class="mb-0">🗂️ Recent Uploads</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th>📄 File Name</th>
                                <th>📅 Uploaded At</th>
                                <th>✅ Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($uploads as $upload)
                                <tr>
                                    <td>{{ $upload->filename }}</td>
                                    <td>{{ $upload->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="text-center">
                                        @if($upload->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($upload->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif


{{-- JS to update template download link --}}
<script>
    document.getElementById('programSelect').addEventListener('change', function () {
        const programId = this.value;
        const downloadBtn = document.getElementById('templateDownloadBtn');
        const baseUrl = downloadBtn.dataset.baseUrl;

        if (programId) {
            downloadBtn.href = `${baseUrl}?program_id=${programId}`;
            downloadBtn.classList.remove('disabled');
            downloadBtn.removeAttribute('disabled');
        } else {
            downloadBtn.href = '#';
            downloadBtn.classList.add('disabled');
            downloadBtn.setAttribute('disabled', true);
        }
    });
</script>
@endsection
