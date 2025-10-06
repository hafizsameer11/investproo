@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')
<h4>User Details</h4>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                <h5><strong>Name:</strong> {{ $user->name }}</h5>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Phone:</strong> {{ $user->phone }}</p>
                <p><strong>User Code:</strong> {{ $user->user_code }}</p>
                <p><strong>Referral Code:</strong> {{ $user->referral_code }}</p>
                <p><strong>Referrals:</strong> {{ $referrals->isEmpty() ? 0 : $referrals->count() }}</p>


                <p><strong>Status:</strong> {{ ucfirst($user->status) }}</p>
                
                @if($user->wallet)
                <div class="alert alert-success mt-3">
                    <h5><i class="fas fa-wallet"></i> <strong>Total Balance: ${{ number_format($user->wallet->total_balance ?? 0, 2) }}</strong></h5>
                </div>
                @endif
                
                <!-- Wallet Update Button -->
                <div class="mt-3">
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateWalletModal">
                        <i class="fas fa-wallet"></i> Update Wallet
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@if ($user->wallet)
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Wallet Information</strong>
                    <span class="badge bg-success float-end">Total: ${{ number_format($user->wallet->total_balance, 2) }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Deposit Amount:</strong> ${{ number_format($user->wallet->deposit_amount, 2) }}</p>
                            <p><strong>Profit Amount:</strong> ${{ number_format($user->wallet->profit_amount, 2) }}</p>
                            <p><strong>Referral Amount:</strong> ${{ number_format($user->wallet->referral_amount, 2) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Withdrawal Amount:</strong> ${{ number_format($user->wallet->withdrawal_amount, 2) }}</p>
                            <p><strong>Bonus Amount:</strong> ${{ number_format($user->wallet->bonus_amount, 2) }}</p>
                            <p><strong>Locked Amount:</strong> ${{ number_format($user->wallet->locked_amount, 2) }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h4 class="text-success">
                            <i class="fas fa-wallet"></i> 
                            Total Balance: ${{ number_format($user->wallet->total_balance, 2) }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><strong>Referrals Summary</strong></div>
            <div class="card-body">
                @php
                    $referralsAmount = $user->transactions->where('type', 'referral')->sum('amount');
                    $totalReferrals = $user->transactions->where('type', 'referral')->where('user_id', $user->id);
                    // $totalReferrals->reference_id;
                @endphp
                <p><strong>Total Referrals:</strong> {{ $referrals->count() }}</p>
                <p><strong>Referral Amount:</strong> {{ $referralsAmount }}</p>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Referral Name</th>
                            <th>Referral Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($totalReferrals as $referral)
                            @php
                                // Fetch the user by reference_id
                                $refUser = \App\Models\User::find($referral->reference_id);
                            @endphp
                            <tr>
                                <td>{{ $refUser ? $refUser->name : 'Unknown' }}</td>
                                <td>{{ number_format($referral->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <p><strong>Referral Details:</strong> </p>

                @if ($referrals->isNotEmpty())
                    <ul>
                        @foreach ($referrals as $ref)
                            <li>Level {{ $ref->referral_level }}: {{ $ref->name }} ({{ $ref->user_code }})</li>
                        @endforeach
                    </ul>
                @else
                    <p>No referrals record.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><strong>Deposit Summary</strong></div>
            <div class="card-body">
                @php

                    $totalDeposit = $user->deposits->where('user_id', $user->id)->sum('amount');
                    $depositPending = $user->deposits->where('status', 'pending')->count();
                    $depositApproved = $user->deposits->where('status', 'approved')->count();
                    $depositRejected = $user->deposits->where('status', 'rejected')->count();
                @endphp
                <p><strong>Total Deposit:</strong> ${{ number_format($totalDeposit, 2) }}</p>
                <p><strong>Pending:</strong> {{ $depositPending }}</p>
                <p><strong>Approved:</strong> {{ $depositApproved }}</p>
                <p><strong>Rejected:</strong> {{ $depositRejected }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><strong>Withdrawal Summary</strong></div>
            <div class="card-body">
                @php
                    $totalWithdrawal = $user->withdrawals->where('user_id', $user->id)->sum('amount');
                    $withdrawalPending = $user->withdrawals->where('status', 'pending')->count();
                    $withdrawalApproved = $user->withdrawals->where('status', 'approved')->count();
                    $withdrawalRejected = $user->withdrawals->where('status', 'rejected')->count();
                @endphp
                <p><strong>Total Withdrawal:</strong> ${{ number_format($totalWithdrawal, 2) }}</p>
                <p><strong>Pending:</strong> {{ $withdrawalPending }}</p>
                <p><strong>Approved:</strong> {{ $withdrawalApproved }}</p>
                <p><strong>Rejected:</strong> {{ $withdrawalRejected }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><strong>Investment Summary</strong></div>
            <div class="card-body">
                @php
                    $totalInvestments = $user->investments->count();
                    $activeInvestments = $user->investments->where('status', 'active')->count();
                    $completedInvestments = $user->investments->where('status', 'completed')->count();
                @endphp
                <p><strong>Total Investments:</strong> {{ $totalInvestments }}</p>
                <p><strong>Active Investments:</strong> {{ $activeInvestments }}</p>
                <p><strong>Completed Investments:</strong> {{ $completedInvestments }}</p>
            </div>
        </div>
    </div>
</div>




<!-- Complete History Tables -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5><strong>Complete Withdrawal History</strong></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Crypto Type</th>
                                <th>Wallet Address</th>
                                <th>Request Date</th>
                                <th>Processed Date</th>
                                <th>Notes</th>
                                <th>Rejection Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->withdrawals as $index => $withdrawal)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
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
                                    <td>{{ $withdrawal->crypto_type ?? 'N/A' }}</td>
                                    <td>{{ $withdrawal->wallet_address ?? 'N/A' }}</td>
                                    <td>{{ $withdrawal->created_at->format('M d, Y H:i') }}</td>
                                    <td>{{ $withdrawal->withdrawal_date ?? 'Not processed' }}</td>
                                    <td>{{ $withdrawal->notes ?? 'No notes' }}</td>
                                    <td>{{ $withdrawal->rejection_reason ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No withdrawal history found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5><strong>Complete Deposit History</strong></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Chain</th>
                                <th>Transaction Hash</th>
                                <th>Request Date</th>
                                <th>Processed Date</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->deposits as $index => $deposit)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>${{ number_format($deposit->amount, 2) }}</td>
                                    <td>
                                        @if ($deposit->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif ($deposit->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $deposit->chain->name ?? 'N/A' }}</td>
                                    <td>{{ $deposit->transaction_hash ?? 'N/A' }}</td>
                                    <td>{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                                    <td>{{ $deposit->updated_at->format('M d, Y H:i') }}</td>
                                    <td>{{ $deposit->notes ?? 'No notes' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No deposit history found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5><strong>Complete Investment History</strong></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Plan Name</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Duration</th>
                                <th>Expected Return</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->investments as $index => $investment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $investment->investmentPlan->name ?? 'N/A' }}</td>
                                    <td>${{ number_format($investment->amount, 2) }}</td>
                                    <td>
                                        @if ($investment->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif ($investment->status === 'completed')
                                            <span class="badge bg-info">Completed</span>
                                        @elseif ($investment->status === 'canceled')
                                            <span class="badge bg-danger">Canceled</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($investment->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $investment->start_date ? \Carbon\Carbon::parse($investment->start_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $investment->end_date ? \Carbon\Carbon::parse($investment->end_date)->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $investment->investmentPlan->duration ?? 'N/A' }} days</td>
                                    <td>${{ number_format($investment->expected_return ?? 0, 2) }}</td>
                                    <td>
                                        @if ($investment->status === 'active')
                                            <span class="text-success">Running</span>
                                        @elseif ($investment->status === 'completed')
                                            <span class="text-info">Completed</span>
                                        @else
                                            <span class="text-muted">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No investment history found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Claimed Amounts Section -->
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-coins"></i> Claimed Amounts by Investment
                </h5>
                <p class="text-muted mb-0">Track all claimed amounts for each investment</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Investment ID</th>
                                <th>Plan Name</th>
                                <th>Investment Amount</th>
                                <th>Expected Return</th>
                                <th>Total Claimed</th>
                                <th>Remaining to Claim</th>
                                <th>Claim Status</th>
                                <th>Last Claim Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->investments as $index => $investment)
                                @php
                                    $totalClaimed = $user->claimedAmounts->where('investment_id', $investment->id)->sum('amount');
                                    $remainingToClaim = max(0, ($investment->expected_return ?? 0) - $totalClaimed);
                                    $lastClaim = $user->claimedAmounts->where('investment_id', $investment->id)->sortByDesc('created_at')->first();
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge bg-primary">#{{ $investment->id }}</span>
                                    </td>
                                    <td>{{ $investment->investmentPlan->name ?? 'N/A' }}</td>
                                    <td>${{ number_format($investment->amount, 2) }}</td>
                                    <td>${{ number_format($investment->expected_return ?? 0, 2) }}</td>
                                    <td>
                                        <span class="text-success fw-bold">${{ number_format($totalClaimed, 2) }}</span>
                                    </td>
                                    <td>
                                        @if ($remainingToClaim > 0)
                                            <span class="text-warning fw-bold">${{ number_format($remainingToClaim, 2) }}</span>
                                        @else
                                            <span class="text-success">Fully Claimed</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($totalClaimed >= ($investment->expected_return ?? 0))
                                            <span class="badge bg-success">Fully Claimed</span>
                                        @elseif ($totalClaimed > 0)
                                            <span class="badge bg-warning">Partially Claimed</span>
                                        @else
                                            <span class="badge bg-danger">Not Claimed</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($lastClaim)
                                            {{ \Carbon\Carbon::parse($lastClaim->created_at)->format('M d, Y H:i') }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No investments found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Claimed Amounts Summary -->
                @if ($user->claimedAmounts->count() > 0)
                <div class="mt-4">
                    <h6 class="text-muted mb-3">Claimed Amounts Details</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Investment ID</th>
                                    <th>Claimed Amount</th>
                                    <th>Reason</th>
                                    <th>Claim Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user->claimedAmounts->sortByDesc('created_at') as $claimed)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">#{{ $claimed->investment_id }}</span>
                                        </td>
                                        <td class="text-success fw-bold">${{ number_format($claimed->amount, 2) }}</td>
                                        <td>{{ $claimed->reason ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($claimed->created_at)->format('M d, Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
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
            <form action="{{ route('admin.user.update-wallet', $user->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <h6><i class="fas fa-wallet"></i> <strong>Current Total Balance: ${{ number_format($user->wallet->total_balance ?? 0, 2) }}</strong></h6>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This will directly update the user's wallet balances. Use with caution.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="deposit_amount" class="form-label">Deposit Amount</label>
                                <input type="number" class="form-control" id="deposit_amount" name="deposit_amount" 
                                       value="{{ $user->wallet->deposit_amount ?? 0 }}" step="0.01" min="0">
                                <small class="text-muted">Current: ${{ number_format($user->wallet->deposit_amount ?? 0, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="profit_amount" class="form-label">Profit Amount</label>
                                <input type="number" class="form-control" id="profit_amount" name="profit_amount" 
                                       value="{{ $user->wallet->profit_amount ?? 0 }}" step="0.01" min="0">
                                <small class="text-muted">Current: ${{ number_format($user->wallet->profit_amount ?? 0, 2) }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="referral_amount" class="form-label">Referral Amount</label>
                                <input type="number" class="form-control" id="referral_amount" name="referral_amount" 
                                       value="{{ $user->wallet->referral_amount ?? 0 }}" step="0.01" min="0">
                                <small class="text-muted">Current: ${{ number_format($user->wallet->referral_amount ?? 0, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bonus_amount" class="form-label">Bonus Amount</label>
                                <input type="number" class="form-control" id="bonus_amount" name="bonus_amount" 
                                       value="{{ $user->wallet->bonus_amount ?? 0 }}" step="0.01" min="0">
                                <small class="text-muted">Current: ${{ number_format($user->wallet->bonus_amount ?? 0, 2) }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="withdrawal_amount" class="form-label">Withdrawal Amount</label>
                                <input type="number" class="form-control" id="withdrawal_amount" name="withdrawal_amount" 
                                       value="{{ $user->wallet->withdrawal_amount ?? 0 }}" step="0.01" min="0">
                                <small class="text-muted">Current: ${{ number_format($user->wallet->withdrawal_amount ?? 0, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="locked_amount" class="form-label">Locked Amount</label>
                                <input type="number" class="form-control" id="locked_amount" name="locked_amount" 
                                       value="{{ $user->wallet->locked_amount ?? 0 }}" step="0.01" min="0">
                                <small class="text-muted">Current: ${{ number_format($user->wallet->locked_amount ?? 0, 2) }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason (Required)</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" 
                                  placeholder="Enter reason for updating wallet balances..." required></textarea>
                    </div>
                    
                    <!-- Wallet Breakdown Summary -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Wallet Breakdown</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Deposit Amount:</small><br>
                                    <strong>${{ number_format($user->wallet->deposit_amount ?? 0, 2) }}</strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Profit Amount:</small><br>
                                    <strong>${{ number_format($user->wallet->profit_amount ?? 0, 2) }}</strong>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Referral Amount:</small><br>
                                    <strong>${{ number_format($user->wallet->referral_amount ?? 0, 2) }}</strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Bonus Amount:</small><br>
                                    <strong>${{ number_format($user->wallet->bonus_amount ?? 0, 2) }}</strong>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Withdrawal Amount:</small><br>
                                    <strong>${{ number_format($user->wallet->withdrawal_amount ?? 0, 2) }}</strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Locked Amount:</small><br>
                                    <strong>${{ number_format($user->wallet->locked_amount ?? 0, 2) }}</strong>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <h5 class="text-success">
                                    <i class="fas fa-wallet"></i> 
                                    Total Balance: ${{ number_format($user->wallet->total_balance ?? 0, 2) }}
                                </h5>
                            </div>
                        </div>
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

@include('admin.footer')
