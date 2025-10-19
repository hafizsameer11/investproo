@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('loyalty.index') }}">Loyalty Management</a></li>
                        <li class="breadcrumb-item active">Edit Tier</li>
                    </ol>
                </div>
                <h4 class="page-title">Edit Loyalty Tier</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('loyalty.update', $loyalty->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tier Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $loyalty->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="days_required" class="form-label">Days Required</label>
                                    <input type="number" class="form-control @error('days_required') is-invalid @enderror" 
                                           id="days_required" name="days_required" value="{{ old('days_required', $loyalty->days_required) }}" required>
                                    @error('days_required')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bonus_percentage" class="form-label">Bonus Percentage</label>
                                    <input type="number" step="0.01" class="form-control @error('bonus_percentage') is-invalid @enderror" 
                                           id="bonus_percentage" name="bonus_percentage" value="{{ old('bonus_percentage', $loyalty->bonus_percentage) }}" required>
                                    @error('bonus_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status</label>
                                    <select class="form-control @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                        <option value="1" {{ old('is_active', $loyalty->is_active) ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !old('is_active', $loyalty->is_active) ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $loyalty->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="text-end">
                            <a href="{{ route('loyalty.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Tier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
