@extends('layouts.admin')
@section('title', 'All Calculated Results (Regular)')

@section('content')

<div class="container py-4">
    <h3 class="mb-4">
        Calculated Results
        <small class="text-muted">Academic Year {{ $academicYear }}</small>
    </h3>

    {{-- ✅ FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Program</th>
                <th>Semester</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($groups as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row->program->name }}</td>
                <td>{{ $row->semester }}</td>
                <td class="d-flex gap-2">

                    {{-- View / Download Result --}}
                    <a href="{{ route('admin.exams.results.show', ['program_id' => $row->program_id, 'semester' => $row->semester]) }}"
                        class="btn btn-sm btn-warning">
                        <i class="bi bi-eye"></i> View Result
                    </a>

                    {{-- Excel Download --}}
                    <a href="{{ route('admin.exams.external-results.download', ['program_id' => $row->program_id, 'semester' => $row->semester]) }}"
                        class="btn btn-sm btn-success">
                        <i class="bi bi-download"></i> Excel
                    </a>

                    {{-- Compile Result Form --}}
                    <form action="{{ route('admin.results.aggregate-all') }}" method="POST">
                        @csrf
                        <input type="hidden" name="program_id" value="{{ $row->program_id }}">
                        <input type="hidden" name="semester" value="{{ $row->semester }}">
                        <button class="btn btn-sm btn-success">Compile Result</button>
                    </form>

                    {{-- Promote Button --}}
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#promotionModal"
                        onclick="setPromotionData({{ $row->program_id }}, {{ $row->semester }}, {{ $row->academic_session_id }})">
                        <i class="bi bi-arrow-up-circle"></i> Promote
                    </button>

                    {{-- Publish Form (commented out)
                    <form method="POST" action="{{ route('admin.exams.results.publish') }}">
                        @csrf
                        <input type="hidden" name="program_id" value="{{ $row->program_id }}">
                        <input type="hidden" name="semester" value="{{ $row->semester }}">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-upload"></i> Publish
                        </button>
                    </form> 
                    --}}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">
                    No results found for {{ $academicYear }}.
                </td>
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
              <!-- Hidden Inputs -->
              <input type="hidden" name="program_id" id="modalProgramId">
              <input type="hidden" name="semester" id="modalSemester">
              <input type="hidden" name="from_session_id" id="modalFromSessionId">

              <!-- New Academic Session -->
              <div class="mb-3">
                <label for="to_session_id" class="form-label">Select New Academic Session</label>
                <select name="to_session_id" id="to_session_id" class="form-select" required>
                  <option value="">-- Select Session --</option>
                  @foreach ($sessions as $session)
                    @if ($session->id !== $currentSession?->id)
                      <option value="{{ $session->id }}">{{ $session->display }}</option>
                    @endif
                  @endforeach
                </select>
              </div>

              <!-- Promotion Type -->
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
function setPromotionData(programId, semester, fromSessionId) {
    document.getElementById('modalProgramId').value = programId;
    document.getElementById('modalSemester').value = semester;
    document.getElementById('modalFromSessionId').value = fromSessionId;
}
</script>
@endpush
