@extends('layouts.admin')
@section('title', 'Manual Promotion')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-primary fw-bold mb-0">📤 Manually Promote Students</h4>
    </div>

    <form method="POST" action="{{ route('admin.promote.manual.submit') }}">
        @csrf

        {{-- Hidden Fields --}}
        <input type="hidden" name="program_id" value="{{ $program_id }}">
        <input type="hidden" name="semester" value="{{ $semester }}">
        <input type="hidden" name="from_session_id" value="{{ $from_session_id }}">
        <input type="hidden" name="to_session_id" value="{{ $to_session_id }}">

        @if ($students->isEmpty())
        <div class="alert alert-warning">No students found for manual promotion.</div>
        @else
        <div class="card shadow-sm animate__animated animate__fadeIn">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-arrow-up-circle me-1"></i> Promote Selected
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                       <thead class="table-light">
    <tr>
        <th>
            <input type="checkbox" id="selectAll" class="form-check-input me-1"> S.No
        </th>
        <th>
            Name<br>
            <input type="text" class="form-control form-control-sm column-filter" data-column="1" placeholder="Search Name">
        </th>
        <th>
            Roll No<br>
            <input type="text" class="form-control form-control-sm column-filter" data-column="2" placeholder="Search Roll No">
        </th>
        <th>
            Result Status<br>
            <select class="form-select form-select-sm column-filter" data-column="3">
                <option value="">All</option>
                <option value="Pass">PASS</option>
                <option value="Fail">FAIL</option>
            </select>
        </th>
        <th>
            Institute<br>
            <input type="text" class="form-control form-control-sm column-filter" data-column="4" placeholder="Search Institute">
        </th>
    </tr>
</thead>


                     <tbody>
    @foreach ($students as $index => $student)
        <tr>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="form-check-input">
                    <span>{{ $index + 1 }}</span>
                </div>
            </td>
            <td>{{ $student->name }}</td>
            <td>{{ $student->nchm_roll_number }}</td>
            <td>
                @php
                    $status = $student->externalResults->firstWhere('semester', $semester)?->result_status ?? 'N/A';
                @endphp
                <span class="badge 
                    {{ $status === 'PASS' ? 'bg-success' : 
                        ($status === 'FAIL' ? 'bg-danger' : 'bg-secondary') }}">
                    {{ ucfirst(strtolower($status)) }}
                </span>
            </td>
            <td>{{ $student->institute->name ?? 'N/A' }}</td>
        </tr>
    @endforeach
</tbody>


                    </table>
                </div>


            </div>
            @endif
    </form>
</div>
@endsection

@push('scripts')

<script>
// Select All Checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="student_ids[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

// Column Filter Function
function filterTable() {
    const filters = document.querySelectorAll('.column-filter');
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        let visible = true;

        filters.forEach(filter => {
            const colIndex = +filter.dataset.column;
            const cell = row.cells[colIndex];
            const cellText = cell?.textContent.trim().toLowerCase() ?? '';
            const filterValue = filter.value.trim().toLowerCase();

            if (filterValue && !cellText.includes(filterValue)) {
                visible = false;
            }
        });

        row.style.display = visible ? '' : 'none';
    });
}

// Attach listeners
document.querySelectorAll('.column-filter').forEach(filter => {
    filter.addEventListener('input', filterTable);
    filter.addEventListener('change', filterTable);
});
</script>


@endpush