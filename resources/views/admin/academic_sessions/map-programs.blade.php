@extends('layouts.admin')
@section('title', 'Map Programs to Session')

@section('content')
<div class="container-fluid px-4 py-4">
    <h4 class="mb-4">
        Map Programs to Session: <strong>{{ $session->year }}</strong>
        @if(request()->has('type'))
            <span class="badge bg-info text-dark text-capitalize ms-2">{{ request('type') }} Program</span>
        @endif
    </h4>

    @forelse($programs as $index => $program)
        <form method="POST" action="{{ route('admin.academic_sessions.mapPrograms.store', $session->id) }}" class="card border shadow-sm mb-4">
            @csrf
            <div class="card-body">
                <h5 class="card-title mb-3 text-primary">{{ $program->name }}</h5>

                <input type="hidden" name="mappings[{{ $index }}][program_id]" value="{{ $program->id }}">

                <div class="mb-3">
                    <label class="form-label">Structure:</label>
                    <select name="mappings[{{ $index }}][structure]" class="form-select" required onchange="toggleFields(this, {{ $program->id }})">
                        <option value="">-- Select --</option>
                        <option value="semester" {{ old("mappings.$index.structure", $program->structure) === 'semester' ? 'selected' : '' }}>Semester-based</option>
                        <option value="yearly" {{ old("mappings.$index.structure", $program->structure) === 'yearly' ? 'selected' : '' }}>Year-based</option>
                    </select>
                </div>

                <div id="fields-{{ $program->id }}" class="mb-3">
                    <div class="semester-field {{ old("mappings.$index.structure", $program->structure) === 'semester' ? '' : 'd-none' }}">
                        <label class="form-label">Semester:</label>
                        <select name="mappings[{{ $index }}][semester]" class="form-select">
                            <option value="">-- Select Semester --</option>
                            @for($i = 1; $i <= 8; $i++)
                                <option value="Sem {{ $i }}" {{ old("mappings.$index.semester") == "Sem $i" ? 'selected' : '' }}>Sem {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <button type="submit" name="selected[]" value="{{ $index }}" class="btn btn-success">
                    <i class="bi bi-link"></i> Map Program
                </button>
            </div>
        </form>
    @empty
        <div class="alert alert-warning">No programs available for mapping.</div>
    @endforelse

    <a href="{{ route('admin.academic_sessions.index') }}" class="btn btn-secondary mt-4">
        <i class="bi bi-arrow-left"></i> Back to Sessions
    </a>
</div>
@endsection

@section('scripts')
<script>
function toggleFields(selectEl, id) {
    const semesterField = document.querySelector(`#fields-${id} .semester-field`);
    if (selectEl.value === 'semester') {
        semesterField.classList.remove('d-none');
    } else {
        semesterField.classList.add('d-none');
    }
}
</script>
@endsection
