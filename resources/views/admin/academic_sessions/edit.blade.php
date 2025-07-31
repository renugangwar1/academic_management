@extends('layouts.admin')
@section('title', isset($academic_session) ? 'Edit Session' : 'Create Session')

@section('content')
<div class="container-fluid px-4 py-4">
    <h4 class="mb-3">{{ isset($academic_session) ? 'Edit' : 'Create' }} Academic Session</h4>

   <form method="POST" action="{{ isset($academic_session) ? route('admin.academic_sessions.update', $academic_session->id) : route('admin.academic_sessions.store') }}">
    @csrf
    @if(isset($academic_session)) @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label">Academic Year (e.g. 2024-25)</label>
        <input type="text" name="year" class="form-control" value="{{ old('year', $academic_session->year ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Term</label>
        <select name="term" class="form-select" required>
            <option value="">-- Select Term --</option>
            <option value="July" {{ old('term', $academic_session->term ?? '') == 'July' ? 'selected' : '' }}>July</option>
            <option value="January" {{ old('term', $academic_session->term ?? '') == 'January' ? 'selected' : '' }}>January</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Program Type</label>
        <select name="type" class="form-select" required>
            <option value="">-- Select Type --</option>
            <option value="diploma" {{ old('type', $academic_session->type ?? '') == 'diploma' ? 'selected' : '' }}>Diploma</option>
            <option value="degree" {{ old('type', $academic_session->type ?? '') == 'degree' ? 'selected' : '' }}>Degree</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Odd/Even</label>
        <select name="odd_even" class="form-select" required>
            <option value="">-- Select --</option>
            <option value="odd" {{ old('odd_even', $academic_session->odd_even ?? '') == 'odd' ? 'selected' : '' }}>Odd</option>
            <option value="even" {{ old('odd_even', $academic_session->odd_even ?? '') == 'even' ? 'selected' : '' }}>Even</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Diploma Year</label>
        <select name="diploma_year" class="form-select" required>
            <option value="">-- Select Year --</option>
            <option value="1" {{ old('diploma_year', $academic_session->diploma_year ?? '') == '1' ? 'selected' : '' }}>1st Year</option>
            <option value="2" {{ old('diploma_year', $academic_session->diploma_year ?? '') == '2' ? 'selected' : '' }}>2nd Year</option>
            <option value="3" {{ old('diploma_year', $academic_session->diploma_year ?? '') == '3' ? 'selected' : '' }}>3rd Year</option>
        </select>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" name="active" value="0">
        <input class="form-check-input" type="checkbox" name="active" id="activeCheck"
            {{ old('active', $academic_session->active ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="activeCheck">
            Set as Active Session
        </label>
    </div>

    <button type="submit" class="btn btn-primary">Save Session</button>
    <a href="{{ route('admin.academic_sessions.index') }}" class="btn btn-secondary">Back</a>
</form>

</div>
@endsection
