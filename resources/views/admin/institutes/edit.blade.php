@extends('layouts.admin')
@section('title', 'Edit Institute')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Institute</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.institutes.update', $institute->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Institute Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $institute->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Institute Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" value="{{ $institute->code }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ $institute->contact_email }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ $institute->contact_phone }}">
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.institutes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Update Institute</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
