@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Investment Control</li>
                    </ol>
                </div>
                <h4 class="page-title">Investment Control</h4>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary bg-soft">
                                <span class="avatar-title rounded-circle bg-primary text-white font-size-18">
                                    <i class="fas fa-chart-line"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-0">Active Investments</p>
                            <h4 class="mb-0">{{ $stats['total_active'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-success bg-soft">
                                <span class="avatar-title rounded-circle bg-success text-white font-size-18">
                                    <i class="fas fa-dollar-sign"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-0">Total Amount</p>
                            <h4 class="mb-0">${{ number_format($stats['total_amount'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-info bg-soft">
                                <span class="avatar-title rounded-circle bg-info text-white font-size-18">
                                    <i class="fas fa-trending-up"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-0">Expected Return</p>
                            <h4 class="mb-0">${{ number_format($stats['total_expected_return'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-warning bg-soft">
                                <span class="avatar-title rounded-circle bg-warning text-white font-size-18">
                                    <i class="fas fa-calendar-day"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-0">Started Today</p>
                            <h4 class="mb-0">{{ $stats['today_started'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Investments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Active Investments Management</h4>
                    <p class="text-muted mb-0">Manage and control all active investments</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Investment ID</th>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Expected Return</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeInvestments as $index => $investment)
                                    @php
                                        $startDate = \Carbon\Carbon::parse($investment->start_date);
                                        $endDate = \Carbon\Carbon::parse($investment->end_date);
                                        $currentDate = \Carbon\Carbon::now();
                                        $totalDays = $startDate->diffInDays($endDate);
                                        $elapsedDays = $startDate->diffInDays($currentDate);
                                        $progress = $totalDays > 0 ? min(100, ($elapsedDays / $totalDays) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-white fw-bold">{{ substr($investment->user->name, 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $investment->user->name }}</h6>
                                                    <small class="text-muted">{{ $investment->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">#{{ $investment->id }}</span>
                                        </td>
                                        <td>{{ $investment->investmentPlan->name ?? 'N/A' }}</td>
                                        <td>${{ number_format($investment->amount, 2) }}</td>
                                        <td>${{ number_format($investment->expected_return, 2) }}</td>
                                        <td>{{ $startDate->format('M d, Y') }}</td>
                                        <td>{{ $endDate->format('M d, Y') }}</td>
                                        <td>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: {{ $progress }}%" 
                                                     aria-valuenow="{{ $progress }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ number_format($progress, 1) }}%</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        onclick="viewInvestmentDetails({{ $investment->id }})">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" 
                                                        onclick="cancelInvestment({{ $investment->id }})">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success" 
                                                        onclick="completeInvestment({{ $investment->id }})">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No active investments found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $activeInvestments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Investment Details Modal -->
<div class="modal fade" id="investmentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Investment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="investmentDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Investment Modal -->
<div class="modal fade" id="cancelInvestmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Investment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelInvestmentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This will cancel the investment and refund the amount to the user's deposit balance.
                    </div>
                    <div class="mb-3">
                        <label for="refund_amount" class="form-label">Refund Amount</label>
                        <input type="number" step="0.01" class="form-control" id="refund_amount" name="refund_amount" 
                               placeholder="Enter refund amount (defaults to investment amount)">
                        <small class="form-text text-muted">Leave empty to refund the full investment amount</small>
                    </div>
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancel_reason" name="reason" rows="4" 
                                  placeholder="Enter reason for cancelling this investment..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Cancel Investment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Investment Modal -->
<div class="modal fade" id="completeInvestmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complete Investment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="completeInvestmentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Info:</strong> This will complete the investment and add the profit to the user's wallet.
                    </div>
                    <div class="mb-3">
                        <label for="final_profit" class="form-label">Final Profit Amount</label>
                        <input type="number" step="0.01" class="form-control" id="final_profit" name="final_profit" 
                               placeholder="Enter final profit amount">
                        <small class="form-text text-muted">Leave empty to use expected return amount</small>
                    </div>
                    <div class="mb-3">
                        <label for="complete_reason" class="form-label">Reason for Completion <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="complete_reason" name="reason" rows="4" 
                                  placeholder="Enter reason for completing this investment..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete Investment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewInvestmentDetails(investmentId) {
    $('#investmentDetailsContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#investmentDetailsModal').modal('show');
    
    $.ajax({
        url: `/investment-control/${investmentId}/details`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const investment = response.data;
                const startDate = new Date(investment.start_date).toLocaleDateString();
                const endDate = new Date(investment.end_date).toLocaleDateString();
                
                $('#investmentDetailsContent').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Investment Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>ID:</strong></td><td>#${investment.id}</td></tr>
                                <tr><td><strong>Plan:</strong></td><td>${investment.investment_plan?.name || 'N/A'}</td></tr>
                                <tr><td><strong>Amount:</strong></td><td>$${parseFloat(investment.amount).toFixed(2)}</td></tr>
                                <tr><td><strong>Expected Return:</strong></td><td>$${parseFloat(investment.expected_return).toFixed(2)}</td></tr>
                                <tr><td><strong>Status:</strong></td><td><span class="badge bg-success">${investment.status}</span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>User Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Name:</strong></td><td>${investment.user?.name || 'N/A'}</td></tr>
                                <tr><td><strong>Email:</strong></td><td>${investment.user?.email || 'N/A'}</td></tr>
                                <tr><td><strong>Start Date:</strong></td><td>${startDate}</td></tr>
                                <tr><td><strong>End Date:</strong></td><td>${endDate}</td></tr>
                            </table>
                        </div>
                    </div>
                    ${investment.claimed_amounts && investment.claimed_amounts.length > 0 ? `
                    <div class="mt-3">
                        <h6>Claimed Amounts</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Amount</th><th>Reason</th><th>Date</th></tr>
                                </thead>
                                <tbody>
                                    ${investment.claimed_amounts.map(claim => `
                                        <tr>
                                            <td>$${parseFloat(claim.amount).toFixed(2)}</td>
                                            <td>${claim.reason || 'N/A'}</td>
                                            <td>${new Date(claim.created_at).toLocaleDateString()}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    ` : ''}
                `);
            }
        },
        error: function() {
            $('#investmentDetailsContent').html('<div class="alert alert-danger">Failed to load investment details</div>');
        }
    });
}

function cancelInvestment(investmentId) {
    $('#cancelInvestmentForm').attr('action', `/investment-control/${investmentId}/cancel`);
    $('#cancelInvestmentModal').modal('show');
}

function completeInvestment(investmentId) {
    $('#completeInvestmentForm').attr('action', `/investment-control/${investmentId}/complete`);
    $('#completeInvestmentModal').modal('show');
}
</script>

@include('admin.footer')
