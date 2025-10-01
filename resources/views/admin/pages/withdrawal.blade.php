@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<h4>Withdrawals</h4>

<div class="row">
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h2>{{ $total_withdrawals }}</h2>
                <h6 class="text-muted">Total Withdrawals</h6>
            </div>
        </div>
    </div> 
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h2>{{ $approved_withdrawals }}</h2>
                <h6 class="text-muted">Approved Withdrawals</h6>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h2>{{ $pending_withdrawals }}</h2>
                <h6 class="text-muted">Pending Withdrawals</h6>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h2>{{ $rejected_withdrawals }}</h2>
                <h6 class="text-muted">Rejected Withdrawals</h6>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-12 col-md-12">
        <table class="table table-striped table-hover table-bordered">
            <thead>
                <tr>
                    <th>Sr No</th>
                    <th>User Name</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($all_withdrawals as $index => $withdrawal)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><a href="{{ route('user.detail', $withdrawal->user->id) }}">{{ $withdrawal->user->name ?? 'N/A' }}</a></td>
                        <td>${{ number_format($withdrawal->amount, 2) }}</td>
                        <td>
                            @if ($withdrawal->status === 'active')
                                <span class="badge bg-success">Approved</span>
                            @elseif ($withdrawal->status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </td>
                        <td>{{ $withdrawal->withdrawal_date ?? 'Not Set' }}</td>
                        <td class="d-flex gap-2">
                            <!-- View Modal Trigger -->
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#viewWithdrawalModal{{ $withdrawal->id }}">
                                View
                            </button>

                            <!-- Approve Button -->
                            @if ($withdrawal->status === 'pending')
                                <form action="{{ route('withdrawals.approve', $withdrawal->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                
                                <!-- Reject Button -->
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#rejectWithdrawalModal{{ $withdrawal->id }}">
                                    Reject
                                </button>
                            @endif

                            <!-- Delete Button -->
                            <form action="{{ route('withdrawals.destroy', $withdrawal->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure?');">
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

<!-- Modals -->
@foreach ($all_withdrawals as $withdrawal)
    <div class="modal fade" id="viewWithdrawalModal{{ $withdrawal->id }}" tabindex="-1"
        aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Withdrawal Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><strong>User Information</strong></h6>
                            <p><strong>Name:</strong> {{ $withdrawal->user->name ?? 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ $withdrawal->user->email ?? 'N/A' }}</p>
                            <p><strong>User Code:</strong> {{ $withdrawal->user->user_code ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>Withdrawal Details</strong></h6>
                            <p><strong>Amount:</strong> ${{ number_format($withdrawal->amount, 2) }}</p>
                            <p><strong>Status:</strong> 
                                @if ($withdrawal->status === 'active')
                                    <span class="badge bg-success">Approved</span>
                                @elseif ($withdrawal->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </p>
                            <p><strong>Request Date:</strong> {{ $withdrawal->created_at->format('M d, Y H:i:s') }}</p>
                            @if ($withdrawal->withdrawal_date)
                                <p><strong>Processed Date:</strong> {{ $withdrawal->withdrawal_date }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6><strong>Transaction Details</strong></h6>
                            <p><strong>Crypto Type:</strong> {{ $withdrawal->crypto_type ?? 'Not specified' }}</p>
                            <p><strong>Wallet Address:</strong> {{ $withdrawal->wallet_address ?? 'Not provided' }}</p>
                            @if ($withdrawal->notes)
                                <p><strong>Notes:</strong> {{ $withdrawal->notes }}</p>
                            @endif
                            @if ($withdrawal->rejection_reason)
                                <p><strong>Rejection Reason:</strong> <span class="text-danger">{{ $withdrawal->rejection_reason }}</span></p>
                            @endif
                        </div>
                    </div>
                    
                    @if ($withdrawal->status === 'pending')
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <strong>Action Required:</strong> This withdrawal is pending approval. 
                                    You can approve or reject this request.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Rejection Modals -->
@foreach ($all_withdrawals as $withdrawal)
    <div class="modal fade" id="rejectWithdrawalModal{{ $withdrawal->id }}" tabindex="-1"
        aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Withdrawal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('withdrawals.reject', $withdrawal->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> Rejecting this withdrawal will refund the amount back to the user's wallet.
                        </div>
                        
                        <div class="mb-3">
                            <label for="rejection_reason{{ $withdrawal->id }}" class="form-label">Rejection Reason (Optional)</label>
                            <textarea class="form-control" id="rejection_reason{{ $withdrawal->id }}" 
                                name="rejection_reason" rows="3" 
                                placeholder="Enter reason for rejection..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Withdrawal Details:</strong>
                            <ul class="list-unstyled mt-2">
                                <li><strong>User:</strong> {{ $withdrawal->user->name ?? 'N/A' }}</li>
                                <li><strong>Amount:</strong> ${{ number_format($withdrawal->amount, 2) }}</li>
                                <li><strong>Wallet Address:</strong> {{ $withdrawal->wallet_address ?? 'N/A' }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@include('admin.footer')
