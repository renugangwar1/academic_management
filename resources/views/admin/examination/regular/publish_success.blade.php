@extends('layouts.admin')
@section('title', 'Results Published')

@section('content')
<div class="container py-5 text-center">
    <h2 class="text-success mb-3"><i class="bi bi-check-lg"></i> Results Published!</h2>
    <p class="lead">Students can now view their marks online.</p>
    <a href="{{ route('admin.exams.calculated') }}" class="btn btn-primary">
        <i class="bi bi-arrow-left-circle"></i> Back to Calculated List
    </a>
</div>
@endsection
