@extends('layouts.admin')
@section('title', isset($academic_session) ? 'Edit Academic Session' : 'Add Academic Session')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h2 class="text-primary fw-bold">
            {{ isset($academic_session) ? 'Edit Academic Session' : 'Add Academic Session' }}
        </h2>
    </div>

    {{-- === Display Validation Errors === --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($academic_session) 
                        ? route('admin.academic_sessions.update', $academic_session->id) 
                        : route('admin.academic_sessions.store') }}" 
          method="POST">
        @csrf
        @if(isset($academic_session))
            @method('PUT')
        @endif

        {{-- 🔸 Academic Year --}}
        <div class="mb-3">
            <label class="form-label">Academic Year</label>
            <input type="text"
                   name="year"
                   class="form-control @error('year') is-invalid @enderror"
                   placeholder="e.g., 2024-2025"
                   value="{{ old('year', $academic_session->year ?? '') }}"
                   required>
            @error('year')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- 🔸 Term --}}
        <div class="mb-3">
            <label class="form-label">Term</label>
            <select name="term" class="form-select @error('term') is-invalid @enderror" required>
                <option value="">Select Term</option>
                <option value="Jan" {{ old('term', $academic_session->term ?? '') === 'Jan' ? 'selected' : '' }}>January</option>
                <option value="July" {{ old('term', $academic_session->term ?? '') === 'July' ? 'selected' : '' }}>July</option>
            </select>
            @error('term')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- 🔸 Program Type --}}
        @php
            $typeValue = request('type') ?? old('type', $academic_session->type ?? '');
        @endphp
        <div class="mb-3">
            <label class="form-label">Program Type</label>
            <select name="type" id="programType" class="form-select @error('type') is-invalid @enderror" required>
                <option value="">Select Type</option>
                <option value="regular" {{ $typeValue === 'regular' ? 'selected' : '' }}>Regular</option>
                <option value="diploma" {{ $typeValue === 'diploma' ? 'selected' : '' }}>Diploma</option>
            </select>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- 🔸 Semester Type (for Regular only) --}}
        <div class="mb-3 d-none" id="semesterField">
            <label class="form-label">Semester Type</label>
            <select name="odd_even"
                    class="form-select @error('odd_even') is-invalid @enderror"
                    id="oddEvenSelect">
                <option value="">Select Semester</option>
                <option value="odd" {{ old('odd_even', $academic_session->odd_even ?? '') === 'odd' ? 'selected' : '' }}>Odd</option>
                <option value="even" {{ old('odd_even', $academic_session->odd_even ?? '') === 'even' ? 'selected' : '' }}>Even</option>
            </select>
            @error('odd_even')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- 🔸 Year of Study (for Diploma only) --}}
        <div class="mb-3 d-none" id="diplomaYearField">
            <label class="form-label">Diploma Year</label>
            <select name="diploma_year"
                    class="form-select @error('diploma_year') is-invalid @enderror"
                    id="diplomaYearSelect">
                <option value="">Select Year</option>
                <option value="1" {{ old('diploma_year', $academic_session->diploma_year ?? '') == '1' ? 'selected' : '' }}>1st Year</option>
                <option value="2" {{ old('diploma_year', $academic_session->diploma_year ?? '') == '2' ? 'selected' : '' }}>2nd Year</option>
            </select>
            @error('diploma_year')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- 🔸 Is Active --}}
        <div class="mb-3">
            <label class="form-label">Active Session</label>
            <select name="active" class="form-select @error('active') is-invalid @enderror" required>
                <option value="1" {{ old('active', $academic_session->active ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ old('active', $academic_session->active ?? 0) == 0 ? 'selected' : '' }}>No</option>
            </select>
            @error('active')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- 🔘 Submit --}}
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                {{ isset($academic_session) ? 'Update Session' : 'Create Session' }}
            </button>
            <a href="{{ route('admin.academic_sessions.index') }}" class="btn btn-secondary">Cancel</a>
        </div>

        {{-- 🔘 Map Programs --}}
        @if(isset($academic_session))
            <div class="mt-3">
                <a href="{{ route('admin.academic_sessions.mapPrograms', [$academic_session->id, 'type' => $typeValue]) }}"
                   class="btn btn-outline-success">
                    <i class="bi bi-link"></i> Map Programs
                </a>
            </div>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
    function toggleFields() {
        const programType = document.getElementById('programType').value;
        const semesterField = document.getElementById('semesterField');
        const diplomaYearField = document.getElementById('diplomaYearField');
        const oddEvenSelect = document.getElementById('oddEvenSelect');
        const diplomaYearSelect = document.getElementById('diplomaYearSelect');

        if (programType === 'regular') {
            semesterField.classList.remove('d-none');
            diplomaYearField.classList.add('d-none');
            oddEvenSelect.setAttribute('required', 'required');
            diplomaYearSelect.removeAttribute('required');
        } else if (programType === 'diploma') {
            semesterField.classList.add('d-none');
            diplomaYearField.classList.remove('d-none');
            diplomaYearSelect.setAttribute('required', 'required');
            oddEvenSelect.removeAttribute('required');
        } else {
            semesterField.classList.add('d-none');
            diplomaYearField.classList.add('d-none');
            oddEvenSelect.removeAttribute('required');
            diplomaYearSelect.removeAttribute('required');
        }
    }

    document.addEventListener('DOMContentLoaded', toggleFields);
    document.getElementById('programType').addEventListener('change', toggleFields);
</script>
@endpush
