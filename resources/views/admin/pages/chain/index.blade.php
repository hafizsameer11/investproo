@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<h4>Chain Management</h4>

<!-- Add New Chain Button -->
<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createChainModal">
    Add New Chain
</button>

<div class="row mt-4">
    <div class="col-lg-12 col-md-12">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Sr No</th>
                    <th>Type</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($chains as $index => $chain)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $chain->type }}</td>
                        <td>{{ $chain->address }}</td>
                        <td>
                            @if ($chain->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="d-flex gap-2">
                            <!-- View -->
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#viewChainModal{{ $chain->id }}">
                                View
                            </button>

                            <!-- Edit -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                data-bs-target="#editChainModal{{ $chain->id }}">
                                Edit
                            </button>

                            <!-- Delete -->
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                data-bs-target="#deleteChainModal{{ $chain->id }}">
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Create Chain Modal -->
<div class="modal fade" id="createChainModal" tabindex="-1" aria-labelledby="createChainLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('chains.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Chain</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <input type="text" name="type" id="type" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" name="address" id="address" class="form-control" required>
                    </div>


                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Create</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>



<!-- View Modals -->
@foreach ($chains as $chain)
    <div class="modal fade" id="viewChainModal{{ $chain->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chain Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Type:</strong> {{ $chain->type }}</p>
                    <p><strong>Address:</strong> {{ $chain->address }}</p>

                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Edit Modals -->
@foreach ($chains as $chain)
    <div class="modal fade" id="editChainModal{{ $chain->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('chains.update', $chain->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Chain</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <input type="text" name="type" value="{{ $chain->type }}" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" name="address" value="{{ $chain->address }}" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" {{ $chain->status == 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="inactive" {{ $chain->status == 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

<!-- Delete Modals -->
@foreach ($chains as $chain)
    <div class="modal fade" id="deleteChainModal{{ $chain->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('chains.destroy', $chain->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this chain?
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

@include('admin.footer')
