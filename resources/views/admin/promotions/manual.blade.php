@extends('layouts.admin')
@section('title', 'Manual Promotion')

@section('content')
<div class="container py-4">
    <h4>Manually Promote Students</h4>
    <form method="POST" action="{{ route('admin.promote.manual.submit') }}">
        @csrf

        <input type="hidden" name="program_id" value="{{ $program_id }}">
        <input type="hidden" name="semester" value="{{ $semester }}">
        <input type="hidden" name="from_session_id" value="{{ $from_session_id }}">
        <input type="hidden" name="to_session_id" value="{{ $to_session_id }}">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Name</th>
                    <th>Roll No</th>
                    <th>Result Status</th>
                    <th>Institute</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                    <tr>
                        <td><input type="checkbox" name="student_ids[]" value="{{ $student->id }}"></td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->nchm_roll_number }}</td>
                        <td>{{ ucfirst($student->result_status) }}</td>
                        <td>{{ $student->institute->name ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Promote Selected</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('selectAll').addEventListener('click', function() {
    document.querySelectorAll('input[name="student_ids[]"]').forEach(cb => cb.checked = this.checked);
});
</script>
@endpush
