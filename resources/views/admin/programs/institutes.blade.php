@extends('layouts.admin')
@section('title', 'Manage Institutes for ' . $program->name)

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="mb-4">
        <h2 class="text-primary fw-bold">Manage Institutes for Program</h2>
        <h4 class="text-dark">Program: <span class="text-secondary">{{ $program->name }}</span></h4>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Map Institutes Form --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-secondary text-white fw-bold rounded-top-4">
            Map More Institutes
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.programs.institutes.update', $program->id) }}">
                @csrf

                {{-- Select All --}}
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="selectAllInstitutes">
                    <label class="form-check-label fw-semibold" for="selectAllInstitutes">
                        Select All Institutes
                    </label>
                </div>

                {{-- Institutes Checkbox List --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Choose Institutes to Map</label>
                    <div id="institutesCheckboxes" class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                        @foreach($allInstitutes as $institute)
                            <div class="form-check">
                                <input 
                                    class="form-check-input institute-checkbox" 
                                    type="checkbox" 
                                    name="institutes[]" 
                                    value="{{ $institute->id }}" 
                                    id="institute_{{ $institute->id }}"
                                    {{ $mappedInstitutes->contains('id', $institute->id) ? 'checked' : '' }}>
                              <label class="form-check-label" for="institute_{{ $institute->id }}">
   ({{ $institute->code }})  <span class="text-muted">{{ $institute->name }}</span>
</label>

                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg fw-semibold rounded-pill">
                        <i class="bi bi-save2 me-2"></i> Save Institute Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>

   {{-- Currently Mapped Institutes --}}
<div class="card mb-5 border-0 shadow-sm rounded-4">
    <div class="card-header bg-primary text-white fw-bold rounded-top-4">
        Currently Mapped Institutes
    </div>
    <div class="card-body">
        @if($mappedInstitutes->count())
            <ul class="list-group list-group-flush">
                @foreach($mappedInstitutes as $institute)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $institute->code }} - {{ $institute->name }}</span>
                        <span class="badge bg-success rounded-pill">Mapped</span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="alert alert-warning mb-0" role="alert">
                No institutes currently mapped to this program.
            </div>
        @endif
    </div>
</div>

</div>

{{-- Scripts --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAllCheckbox = document.getElementById('selectAllInstitutes');
        const instituteCheckboxes = document.querySelectorAll('.institute-checkbox');

        // Toggle all checkboxes on "Select All" click
        selectAllCheckbox.addEventListener('change', function () {
            instituteCheckboxes.forEach(cb => cb.checked = this.checked);
        });

        // If any checkbox is unchecked, uncheck "Select All"
        instituteCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const allChecked = Array.from(instituteCheckboxes).every(input => input.checked);
                selectAllCheckbox.checked = allChecked;
            });
        });

        // Initialize "Select All" checkbox state
        const allChecked = Array.from(instituteCheckboxes).every(input => input.checked);
        selectAllCheckbox.checked = allChecked;
    });
</script>
@endsection
