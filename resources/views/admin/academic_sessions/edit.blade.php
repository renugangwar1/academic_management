@extends('layouts.admin')
@section('title', isset($academic_session) ? 'Edit Session' : 'Create Session')

@section('content')
<div class="container">
    <h4 class="mb-3">{{ isset($academic_session) ? 'Edit' : 'Create' }} Academic Session</h4>

    <form method="POST" action="{{ isset($academic_session) ? route('admin.academic_sessions.update', $academic_session->id) : route('admin.academic_sessions.store') }}">
        @csrf
        @if(isset($academic_session)) @method('PUT') @endif

        <div class="mb-3">
            <label class="form-label">Academic Year (e.g. 2024-25)</label>
            <input type="text" name="year" class="form-control" value="{{ old('year', $academic_session->year ?? '') }}" required>
        </div>

      
        <div class="form-check mb-3">
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
