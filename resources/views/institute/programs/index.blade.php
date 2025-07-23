@extends('layouts.institute')

@section('title', 'My Programs')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">🎓 My Programs</h2>
        <a href="{{ route('institute.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            ⬅️ Back to Dashboard
        </a>
    </div>

    @if($programs->isEmpty())
        <div class="alert alert-info text-center shadow-sm">
            <i class="bi bi-info-circle"></i> No programs found for your institute.
        </div>
    @else
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                         <th scope="col">Program ID</th>
                        <th scope="col"> Name</th>
                        <th scope="col"> Duration</th>
                        <th scope="col"> Structure</th>
                        <th scope="col"> Courses Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($programs as $program)
                        <tr>
                            <td><span class="badge bg-warning text-dark">{{ $program->id }}</span></td>
                            <td>{{ $program->name }}</td>
                            <td>{{ $program->duration }} {{ ucfirst($program->duration_unit) }}</td>
                            <td>{{ ucfirst($program->structure) }}</td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $program->courses->count() }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
