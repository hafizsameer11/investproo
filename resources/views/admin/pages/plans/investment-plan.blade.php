@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<h4>Investment Plans</h4>
<!-- Add New Plan Button -->
<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createPlanModal">
    Add New Plan
</button>

<div class="row mt-4">
    <div class="col-lg-12 col-md-12">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Sr No</th>
                    <th>Plan Name</th>
                    <th>Min Amount</th>
                    <th>Max Amount</th>
                    <th>Profit %</th>
                    <th>Duration (Months)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plans as $index => $plan)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $plan->plan_name }}</td>
                        <td>${{ number_format($plan->min_amount, 2) }}</td>
                        <td>${{ number_format($plan->max_amount, 2) }}</td>
                        <td>{{ $plan->profit_percentage }}%</td>
                        <td>{{ $plan->duration }} Months</td>
                        <td>
                            @if ($plan->status == 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="d-flex gap-2">
                            <!-- View -->
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#viewPlanModal{{ $plan->id }}">
                                View
                            </button>

                            <!-- Edit -->
                            <!-- Edit Button -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                data-bs-target="#editPlanModal{{ $plan->id }}">
                                Edit
                            </button>

                            <!-- Delete -->
                            <form action="{{ route('plans.destroy', $plan->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this plan?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Create Plan Modal -->
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('plans.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="plan_name" class="form-label">Plan Name</label>
                        <input type="text" name="plan_name" id="plan_name" class="form-control"
                             required>
                    </div>

                    <div class="mb-3">
                        <label for="min_amount" class="form-label">Min Amount</label>
                        <input type="number" step="0.01" name="min_amount" id="min_amount" class="form-control"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="max_amount" class="form-label">Max Amount</label>
                        <input type="number" step="0.01" name="max_amount" id="max_amount" class="form-control"
                          required>
                    </div>

                    <div class="mb-3">
                        <label for="profit_percentage" class="form-label">Profit Percentage</label>
                        <input type="number" step="0.01" name="profit_percentage" id="profit_percentage"
                            class="form-control" 
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="duration" class="form-label">Duration (Month)</label>
                        <input type="number" name="duration" id="duration" class="form-control"
                             required>
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
@foreach ($plans as $plan)
    <div class="modal fade" id="viewPlanModal{{ $plan->id }}" tabindex="-1" aria-labelledby="viewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Plan Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Name:</strong> {{ $plan->plan_name }}</p>
                    <p><strong>Min Amount:</strong> ${{ $plan->min_amount }}</p>
                    <p><strong>Max Amount:</strong> ${{ $plan->max_amount }}</p>
                    <p><strong>Profit Percentage:</strong> {{ $plan->profit_percentage }}%</p>
                    <p><strong>Duration:</strong> {{ $plan->duration }} days</p>
                    <p><strong>Status:</strong> {{ ucfirst($plan->status) }}</p>
                </div>
            </div>
        </div>
    </div>
@endforeach

@foreach ($plans as $plan)
<!-- existing View Plan Modal -->

<!-- Edit Plan Modal -->
<div class="modal fade" id="editPlanModal{{ $plan->id }}" tabindex="-1" aria-labelledby="editPlanLabel{{ $plan->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('plans.update', $plan->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Plan - {{ $plan->plan_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin.pages.plans._form', ['plan' => $plan])
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


@include('admin.footer')
