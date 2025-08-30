@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<h4>Withdrawals</h4>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h2>{{ $total_withdrawals }}</h2>
                <h6 class="text-muted">Total Withdrawals</h6>
            </div>
        </div>
    </div> 
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h2>{{ $approved_withdrawals }}</h2>
                <h6 class="text-muted">Approved Withdrawals</h6>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h2>{{ $pending_withdrawals }}</h2>
                <h6 class="text-muted">Pending Withdrawals</h6>
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

                            <!-- Verify Button -->
                            @if ($withdrawal->status !== 'active')
                                <form action="{{ route('withdrawals.approve', $withdrawal->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
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
                    <p><strong>User:</strong>{{ $withdrawal->user->name ?? 'N/A' }}</p>
                    <p><strong>Amount:</strong> ${{ $withdrawal->amount }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($withdrawal->status) }}</p>
                    <p><strong>Date:</strong> {{ $withdrawal->withdrawal_date ?? 'Not set' }}</p>
                </div>
            </div>
        </div>
    </div>
@endforeach

@include('admin.footer')
