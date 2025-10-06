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
                        <li class="breadcrumb-item active">Mining Control</li>
                    </ol>
                </div>
                <h4 class="page-title">Mining Sessions Control</h4>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary bg-soft">
                                <span class="avatar-title rounded-circle bg-primary text-white font-size-18">
                                    <i class="fas fa-cog"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-0">Total Sessions</p>
                            <h4 class="mb-0">{{ $stats['total_sessions'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-success bg-soft">
                                <span class="avatar-title rounded-circle bg-success text-white font-size-18">
                                    <i class="fas fa-play"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-0">Active</p>
                            <h4 class="mb-0">{{ $stats['active_sessions'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-info bg-soft">
                                <span class="avatar-title rounded-circle bg-info text-white font-size-18">
                                    <i class="fas fa-check"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-0">Completed</p>
                            <h4 class="mb-0">{{ $stats['completed_sessions'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-warning bg-soft">
                                <span class="avatar-title rounded-circle bg-warning text-white font-size-18">
                                    <i class="fas fa-dollar-sign"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-0">Total Rewards</p>
                            <h4 class="mb-0">${{ number_format($stats['total_rewards'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-success bg-soft">
                                <span class="avatar-title rounded-circle bg-success text-white font-size-18">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-0">Claimed</p>
                            <h4 class="mb-0">${{ number_format($stats['claimed_rewards'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-danger bg-soft">
                                <span class="avatar-title rounded-circle bg-danger text-white font-size-18">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-0">Unclaimed</p>
                            <h4 class="mb-0">${{ number_format($stats['unclaimed_rewards'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mining Sessions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Mining Sessions Management</h4>
                    <p class="text-muted mb-0">Control and manage all mining sessions</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Session ID</th>
                                    <th>Investment ID</th>
                                    <th>Status</th>
                                    <th>Rewards Earned</th>
                                    <th>Rewards Claimed</th>
                                    <th>Started At</th>
                                    <th>Stopped At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($miningSessions as $index => $session)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-white fw-bold">{{ substr($session->user->name, 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $session->user->name }}</h6>
                                                    <small class="text-muted">{{ $session->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">#{{ $session->id }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">#{{ $session->investment_id }}</span>
                                        </td>
                                        <td>
                                            @if($session->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif($session->status === 'completed')
                                                <span class="badge bg-info">Completed</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($session->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold">${{ number_format($session->rewards_earned, 2) }}</span>
                                        </td>
                                        <td>
                                            @if($session->rewards_claimed)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-warning">No</span>
                                            @endif
                                        </td>
                                        <td>{{ $session->started_at ? \Carbon\Carbon::parse($session->started_at)->format('M d, Y H:i') : 'N/A' }}</td>
                                        <td>{{ $session->stopped_at ? \Carbon\Carbon::parse($session->stopped_at)->format('M d, Y H:i') : 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        onclick="viewSessionDetails({{ $session->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($session->status === 'active')
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="deactivateSession({{ $session->id }})">
                                                        <i class="fas fa-pause"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="activateSession({{ $session->id }})">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                @endif
                                                @if(!$session->rewards_claimed)
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            onclick="forceClaimRewards({{ $session->id }})">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="deleteSession({{ $session->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No mining sessions found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $miningSessions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Session Details Modal -->
<div class="modal fade" id="sessionDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mining Session Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="sessionDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Rewards Modal -->
<div class="modal fade" id="updateRewardsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Mining Rewards</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateRewardsForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rewards_earned" class="form-label">Rewards Earned <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="rewards_earned" name="rewards_earned" 
                               placeholder="Enter new rewards amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="update_reason" class="form-label">Reason for Update <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="update_reason" name="reason" rows="3" 
                                  placeholder="Enter reason for updating rewards..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Rewards</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Session Modal -->
<div class="modal fade" id="deleteSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Mining Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteSessionForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action cannot be undone. The mining session will be permanently deleted.
                    </div>
                    <div class="mb-3">
                        <label for="delete_reason" class="form-label">Reason for Deletion <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="delete_reason" name="reason" rows="3" 
                                  placeholder="Enter reason for deleting this session..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Force Claim Modal -->
<div class="modal fade" id="forceClaimModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Force Claim Mining Rewards</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="forceClaimForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Info:</strong> This will add the mining rewards to the user's profit balance.
                    </div>
                    <div class="mb-3">
                        <label for="claim_reason" class="form-label">Reason for Force Claim <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="claim_reason" name="reason" rows="3" 
                                  placeholder="Enter reason for force claiming rewards..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Force Claim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Activate/Deactivate Modal -->
<div class="modal fade" id="sessionActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionActionTitle">Session Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sessionActionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert" id="sessionActionAlert">
                        <i class="fas fa-info-circle"></i>
                        <span id="sessionActionText">Action description</span>
                    </div>
                    <div class="mb-3">
                        <label for="action_reason" class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="action_reason" name="reason" rows="3" 
                                  placeholder="Enter reason for this action..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="sessionActionButton">Action</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewSessionDetails(sessionId) {
    $('#sessionDetailsContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#sessionDetailsModal').modal('show');
    
    $.ajax({
        url: `/mining-control/${sessionId}/details`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const session = response.data;
                const startedAt = session.started_at ? new Date(session.started_at).toLocaleString() : 'N/A';
                const stoppedAt = session.stopped_at ? new Date(session.stopped_at).toLocaleString() : 'N/A';
                
                $('#sessionDetailsContent').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Session Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>ID:</strong></td><td>#${session.id}</td></tr>
                                <tr><td><strong>Investment ID:</strong></td><td>#${session.investment_id}</td></tr>
                                <tr><td><strong>Status:</strong></td><td><span class="badge bg-${session.status === 'active' ? 'success' : 'info'}">${session.status}</span></td></tr>
                                <tr><td><strong>Rewards Earned:</strong></td><td>$${parseFloat(session.rewards_earned).toFixed(2)}</td></tr>
                                <tr><td><strong>Rewards Claimed:</strong></td><td><span class="badge bg-${session.rewards_claimed ? 'success' : 'warning'}">${session.rewards_claimed ? 'Yes' : 'No'}</span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Timing Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Started At:</strong></td><td>${startedAt}</td></tr>
                                <tr><td><strong>Stopped At:</strong></td><td>${stoppedAt}</td></tr>
                                <tr><td><strong>Created At:</strong></td><td>${new Date(session.created_at).toLocaleString()}</td></tr>
                                <tr><td><strong>Updated At:</strong></td><td>${new Date(session.updated_at).toLocaleString()}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6>User Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Name:</strong></td><td>${session.user?.name || 'N/A'}</td></tr>
                                <tr><td><strong>Email:</strong></td><td>${session.user?.email || 'N/A'}</td></tr>
                            </table>
                        </div>
                    </div>
                `);
            }
        },
        error: function() {
            $('#sessionDetailsContent').html('<div class="alert alert-danger">Failed to load session details</div>');
        }
    });
}

function updateRewards(sessionId) {
    $('#updateRewardsForm').attr('action', `/mining-control/${sessionId}/update-rewards`);
    $('#updateRewardsModal').modal('show');
}

function deleteSession(sessionId) {
    $('#deleteSessionForm').attr('action', `/mining-control/${sessionId}/delete`);
    $('#deleteSessionModal').modal('show');
}

function forceClaimRewards(sessionId) {
    $('#forceClaimForm').attr('action', `/mining-control/${sessionId}/force-claim`);
    $('#forceClaimModal').modal('show');
}

function activateSession(sessionId) {
    $('#sessionActionTitle').text('Activate Mining Session');
    $('#sessionActionText').text('This will activate the mining session and start the mining process.');
    $('#sessionActionAlert').removeClass('alert-warning alert-info').addClass('alert-success');
    $('#sessionActionButton').removeClass('btn-warning btn-info').addClass('btn-success').text('Activate');
    $('#sessionActionForm').attr('action', `/mining-control/${sessionId}/activate`);
    $('#sessionActionModal').modal('show');
}

function deactivateSession(sessionId) {
    $('#sessionActionTitle').text('Deactivate Mining Session');
    $('#sessionActionText').text('This will deactivate the mining session and stop the mining process.');
    $('#sessionActionAlert').removeClass('alert-success alert-info').addClass('alert-warning');
    $('#sessionActionButton').removeClass('btn-success btn-info').addClass('btn-warning').text('Deactivate');
    $('#sessionActionForm').attr('action', `/mining-control/${sessionId}/deactivate`);
    $('#sessionActionModal').modal('show');
}
</script>

@include('admin.footer')
