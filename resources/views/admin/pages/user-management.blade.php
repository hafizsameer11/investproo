@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> User Management
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ $users->total() }} Total Users</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Users with Missed Claims Alert -->
                    @if($usersWithMissedClaims->count() > 0)
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Users with Missed Claims</h5>
                        <p>{{ $usersWithMissedClaims->count() }} users have completed investments but haven't claimed their amounts.</p>
                    </div>
                    @endif

                    <!-- User Management Tabs -->
                    <ul class="nav nav-tabs" id="userTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="all-users-tab" data-toggle="tab" href="#all-users" role="tab">
                                All Users ({{ $users->total() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="missed-claims-tab" data-toggle="tab" href="#missed-claims" role="tab">
                                Missed Claims ({{ $usersWithMissedClaims->count() }})
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="userTabsContent">
                        <!-- All Users Tab -->
                        <div class="tab-pane fade show active" id="all-users" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>Wallet Balance</th>
                                            <th>Active Investments</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <span class="text-white fw-bold">{{ substr($user->name, 0, 1) }}</span>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $user->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $user->user_code }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                <span class="badge badge-success">${{ number_format($user->wallet->total_balance ?? 0, 2) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $user->investments->where('status', 'active')->count() }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="viewUserDetails({{ $user->id }})">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning" onclick="updateWallet({{ $user->id }})">
                                                        <i class="fas fa-wallet"></i> Wallet
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-info" onclick="checkClaimableAmounts({{ $user->id }})">
                                                        <i class="fas fa-coins"></i> Claims
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            {{ $users->links() }}
                        </div>

                        <!-- Missed Claims Tab -->
                        <div class="tab-pane fade" id="missed-claims" role="tabpanel">
                            @if($usersWithMissedClaims->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User</th>
                                            <th>Completed Investments</th>
                                            <th>Expected Returns</th>
                                            <th>Claimed Amounts</th>
                                            <th>Missed Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($usersWithMissedClaims as $user)
                                        @php
                                            $totalExpected = $user->investments->sum('expected_return');
                                            $totalClaimed = $user->claimedAmounts->sum('amount');
                                            $missedAmount = $totalExpected - $totalClaimed;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <span class="text-white fw-bold">{{ substr($user->name, 0, 1) }}</span>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $user->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $user->investments->count() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">${{ number_format($totalExpected, 2) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">${{ number_format($totalClaimed, 2) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-danger">${{ number_format($missedAmount, 2) }}</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success" onclick="helpClaimAmount({{ $user->id }})">
                                                    <i class="fas fa-hand-holding-usd"></i> Help Claim
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>No Missed Claims</h5>
                                <p class="text-muted">All users have claimed their amounts properly.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Help Claim Amount Modal -->
<div class="modal fade" id="helpClaimModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Help User Claim Amount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="helpClaimForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> This will help the user claim their missed amount and add it to their wallet.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="investment_id" class="form-label">Investment</label>
                                <select class="form-control" id="investment_id" name="investment_id" required>
                                    <option value="">Select Investment</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount to Claim</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Enter reason for helping claim this amount..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Help Claim Amount</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Wallet Modal -->
<div class="modal fade" id="updateWalletModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update User Wallet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateWalletForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This will directly update the user's wallet balances. Use with caution.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="deposit_amount" class="form-label">Deposit Amount</label>
                                <input type="number" class="form-control" id="deposit_amount" name="deposit_amount" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="profit_amount" class="form-label">Profit Amount</label>
                                <input type="number" class="form-control" id="profit_amount" name="profit_amount" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="referral_amount" class="form-label">Referral Amount</label>
                                <input type="number" class="form-control" id="referral_amount" name="referral_amount" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bonus_amount" class="form-label">Bonus Amount</label>
                                <input type="number" class="form-control" id="bonus_amount" name="bonus_amount" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="withdrawal_amount" class="form-label">Withdrawal Amount</label>
                                <input type="number" class="form-control" id="withdrawal_amount" name="withdrawal_amount" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="locked_amount" class="form-label">Locked Amount</label>
                                <input type="number" class="form-control" id="locked_amount" name="locked_amount" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="wallet_reason" class="form-label">Reason (Required)</label>
                        <textarea class="form-control" id="wallet_reason" name="reason" rows="3" placeholder="Enter reason for updating wallet balances..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Wallet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Check Claimable Amounts Modal -->
<div class="modal fade" id="claimableAmountsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Claimable Amounts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="claimableAmountsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentUserId = null;

function viewUserDetails(userId) {
    window.location.href = `/admin/users/${userId}`;
}

function updateWallet(userId) {
    currentUserId = userId;
    $('#updateWalletModal').modal('show');
}

function helpClaimAmount(userId) {
    currentUserId = userId;
    
    // Load user's completed investments
    fetch(`/admin/user-management/${userId}/claimable-amounts`)
        .then(response => response.json())
        .then(data => {
            const investmentSelect = document.getElementById('investment_id');
            investmentSelect.innerHTML = '<option value="">Select Investment</option>';
            
            data.claimable_amounts.forEach(item => {
                const option = document.createElement('option');
                option.value = item.investment.id;
                option.textContent = `Investment #${item.investment.id} - Expected: $${item.total_expected.toFixed(2)} - Claimable: $${item.claimable_amount.toFixed(2)}`;
                investmentSelect.appendChild(option);
            });
            
            $('#helpClaimModal').modal('show');
        })
        .catch(error => {
            console.error('Error loading claimable amounts:', error);
            alert('Error loading user data');
        });
}

function checkClaimableAmounts(userId) {
    currentUserId = userId;
    
    fetch(`/admin/user-management/${userId}/claimable-amounts`)
        .then(response => response.json())
        .then(data => {
            let content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>User Information</strong></h6>
                        <p><strong>Name:</strong> ${data.user.name}</p>
                        <p><strong>Email:</strong> ${data.user.email}</p>
                        <p><strong>User Code:</strong> ${data.user.user_code}</p>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>Wallet Balance</strong></h6>
                        <p><strong>Total Balance:</strong> $${(data.user.wallet?.total_balance || 0).toFixed(2)}</p>
                        <p><strong>Profit Amount:</strong> $${(data.user.wallet?.profit_amount || 0).toFixed(2)}</p>
                    </div>
                </div>
                <hr>
            `;
            
            if (data.claimable_amounts.length > 0) {
                content += `
                    <h6><strong>Claimable Amounts</strong></h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Investment ID</th>
                                    <th>Total Expected</th>
                                    <th>Total Claimed</th>
                                    <th>Claimable Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.claimable_amounts.forEach(item => {
                    content += `
                        <tr>
                            <td>#${item.investment.id}</td>
                            <td>$${item.total_expected.toFixed(2)}</td>
                            <td>$${item.total_claimed.toFixed(2)}</td>
                            <td><span class="badge badge-success">$${item.claimable_amount.toFixed(2)}</span></td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="quickClaimAmount(${item.investment.id}, ${item.claimable_amount})">
                                    <i class="fas fa-hand-holding-usd"></i> Help Claim
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                content += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                content += `
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>No Claimable Amounts</h5>
                        <p class="text-muted">This user has no claimable amounts or has already claimed everything.</p>
                    </div>
                `;
            }
            
            document.getElementById('claimableAmountsContent').innerHTML = content;
            $('#claimableAmountsModal').modal('show');
        })
        .catch(error => {
            console.error('Error loading claimable amounts:', error);
            document.getElementById('claimableAmountsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error loading user data. Please try again.
                </div>
            `;
        });
}

function quickClaimAmount(investmentId, amount) {
    document.getElementById('investment_id').value = investmentId;
    document.getElementById('amount').value = amount;
    $('#claimableAmountsModal').modal('hide');
    $('#helpClaimModal').modal('show');
}

// Form submissions
document.getElementById('helpClaimForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`/admin/user-management/${currentUserId}/help-claim`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('success')) {
            location.reload();
        } else {
            alert('Error: ' + data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error helping claim amount');
    });
});

document.getElementById('updateWalletForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`/admin/user-management/${currentUserId}/update-wallet`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('success')) {
            location.reload();
        } else {
            alert('Error: ' + data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating wallet');
    });
});
</script>
@include('admin.footer')
