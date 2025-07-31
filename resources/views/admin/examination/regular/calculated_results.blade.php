@extends('layouts.admin')
@section('title', 'All Calculated Results (Regular)')

@section('content')
<div class="container-fluid px-4 py-4">
    <h3 class="mb-4">All Calculated Results (Regular)</h3>

    {{-- ✅ Flash Messages --}}
    @foreach (['success', 'error', 'warning'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg === 'error' ? 'danger' : $msg }} alert-dismissible fade show" role="alert">
                {{ session($msg) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Program</th>
                <th>Semester</th>
                <th>Academic Year</th>
                <th>Term</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($groups as $row)
            @php
    $studentsCount = \App\Models\Student::where([
        ['program_id', $row->program_id],
        ['semester', $row->current_semester],
        ['academic_session_id', $row->academic_session_id],
    ])->count();

    $promotedCount = \App\Models\StudentSessionHistory::where([
        ['program_id', $row->program_id],
        ['from_semester', $row->current_semester],
        ['academic_session_id', $row->academic_session_id],
    ])->count();

    $alreadyPromoted = $studentsCount > 0 && $studentsCount === $promotedCount;
@endphp

            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row->program->name ?? 'N/A' }}</td>
                <td>{{ $row->current_semester }}</td>
                <td>{{ $row->academicSession->year ?? 'N/A' }}</td>
                <td>{{ $row->academicSession->term ?? 'N/A' }} ({{ ucfirst($row->academicSession->odd_even ?? '') }})</td>

                <td class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.exams.results.show', [
                        'program_id' => $row->program_id,
                        'semester' => $row->current_semester,
                        'academic_session_id' => $row->academic_session_id
                    ]) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-eye"></i> View Result
                    </a>

                  <a href="{{ route('admin.exams.external-results.download', [
    'academic_session_id' => $row->academic_session_id,
    'program_id' => $row->program_id,
    'semester' => $row->current_semester
]) }}" class="btn btn-sm btn-success">
    <i class="bi bi-download"></i> Excel (External)
</a>


                    <form action="{{ route('admin.results.aggregate-all') }}" method="POST">
                        @csrf
                        <input type="hidden" name="program_id" value="{{ $row->program_id }}">
                        <input type="hidden" name="semester" value="{{ $row->current_semester }}">
                        <input type="hidden" name="academic_session_id" value="{{ $row->academic_session_id }}">
                        <input type="hidden" name="action" value="compile">
                        <button class="btn btn-sm btn-success">Compile Result</button>
                    </form>

                    @if ($alreadyPromoted)
                        <button class="btn btn-sm btn-secondary" disabled title="Already Promoted">
                            <i class="bi bi-check-circle"></i> Promoted
                        </button>
                    @else
                        <button type="button"
                            class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#promotionModal"
                            onclick="setPromotionData({{ $row->program_id }}, {{ $row->current_semester }}, {{ $row->academic_session_id }})">
                            <i class="bi bi-arrow-up-circle"></i> Promote
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No calculated results found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <!-- Promotion Modal -->
    <div class="modal fade" id="promotionModal" tabindex="-1" aria-labelledby="promotionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.promote') }}" id="promotionForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="promotionModalLabel">Promote Students</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="program_id" id="modalProgramId">
                        <input type="hidden" name="semester" id="modalSemester">
                        <input type="hidden" name="from_session_id" id="modalFromSessionId">

                        <div class="mb-3">
                            <label for="to_session_id" class="form-label">Promote To (New Academic Session)</label>
                            <select name="to_session_id" id="to_session_id" class="form-select" required>
                                <option value="">-- Select Session --</option>
                                @foreach ($sessions as $session)
                                    <option value="{{ $session->id }}">{{ $session->display }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Only sessions after the current session are shown.</small>
                        </div>

                        <div class="mb-3">
                            <label for="promotion_type" class="form-label">Promotion Type</label>
                            <select name="promotion_type" id="promotion_type" class="form-select" required>
                                <option value="">-- Select Type --</option>
                                <option value="all">Promote All</option>
                                <option value="passed">Promote Passed Only</option>
                                <option value="failed">Promote Failed / Reappear</option>
                                <option value="manual">Promote Manually</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Proceed</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ✅ Set modal values when Promote button is clicked
function setPromotionData(programId, semester, fromSessionId) {
    document.getElementById('modalProgramId').value = programId;
    document.getElementById('modalSemester').value = semester;
    document.getElementById('modalFromSessionId').value = fromSessionId;
}

document.getElementById('promotionForm').addEventListener('submit', function (e) {
    const fromSession = parseInt(document.getElementById('modalFromSessionId').value);
    const toSession = parseInt(document.getElementById('to_session_id').value);
    const promotionType = document.getElementById('promotion_type').value;

    if (isNaN(fromSession) || isNaN(toSession) || toSession <= fromSession) {
        e.preventDefault();
        alert('⚠️ Please select a valid future academic session.');
        return;
    }

    if (promotionType === 'manual') {
        e.preventDefault(); // Stop form submission

        const programId = document.getElementById('modalProgramId').value;
        const semester = document.getElementById('modalSemester').value;

        const manualUrl = `{{ url('/admin/promotions/manual') }}?program_id=${programId}&semester=${semester}&from_session_id=${fromSession}&to_session_id=${toSession}`;
        window.location.href = manualUrl;
    }
});
</script>
@endpush
