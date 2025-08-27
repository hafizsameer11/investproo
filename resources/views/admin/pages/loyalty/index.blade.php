@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')
<h4>Loyalty Management</h4>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Loyalty Tiers Management</h3>
                    <div class="card-tools">
                        <a href="{{ route('loyalty.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Tier
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Days Required</th>
                                    <th>Bonus Percentage</th>
                                    <th>Status</th>
                                    <th>Description</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loyalties as $loyalty)
                                    <tr>
                                        <td>{{ $loyalty->id }}</td>
                                        <td>{{ $loyalty->name }}</td>
                                        <td>{{ $loyalty->days_required }} days</td>
                                        <td>{{ $loyalty->bonus_percentage }}%</td>
                                        <td>
                                            @if($loyalty->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($loyalty->description, 50) }}</td>
                                        <td>{{ $loyalty->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('loyalty.edit', $loyalty->id) }}" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('loyalty.toggle-status', $loyalty->id) }}" 
                                                      method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Toggle Status">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('loyalty.destroy', $loyalty->id) }}" 
                                                      method="POST" style="display: inline;"
                                                      onsubmit="return confirm('Are you sure you want to delete this loyalty tier?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No loyalty tiers found.</td>
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
