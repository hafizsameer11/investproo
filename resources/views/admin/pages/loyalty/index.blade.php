@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Loyalty Management</li>
                    </ol>
                </div>
                <h4 class="page-title">Loyalty Management</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <h4 class="header-title">Loyalty Tiers</h4>
                            <p class="text-muted">Manage loyalty tiers and their bonus percentages</p>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end">
                                <a href="{{ route('loyalty.create') }}" class="btn btn-primary">
                                    <i class="mdi mdi-plus"></i> Add New Tier
                                </a>
                            </div>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Days Required</th>
                                    <th>Bonus Percentage</th>
                                    <th>Status</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loyalties as $loyalty)
                                <tr>
                                    <td>{{ $loyalty->name }}</td>
                                    <td>{{ $loyalty->days_required }}</td>
                                    <td>{{ $loyalty->bonus_percentage }}%</td>
                                    <td>
                                        <span class="badge badge-{{ $loyalty->is_active ? 'success' : 'danger' }}">
                                            {{ $loyalty->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ Str::limit($loyalty->description, 50) }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('loyalty.edit', $loyalty->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-pencil"></i> Edit
                                            </a>
                                            <form action="{{ route('loyalty.toggle-status', $loyalty->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-outline-{{ $loyalty->is_active ? 'warning' : 'success' }}">
                                                    <i class="mdi mdi-{{ $loyalty->is_active ? 'pause' : 'play' }}"></i>
                                                    {{ $loyalty->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('loyalty.destroy', $loyalty->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this loyalty tier?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="mdi mdi-delete"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No loyalty tiers found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
