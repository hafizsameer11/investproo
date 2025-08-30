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
            </div>
        </div>
    </div>
</div>
@if ($user->wallet)
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header"><strong>Wallet Information</strong></div>
                <div class="card-body">
                    <p><strong>Deposit Amount:</strong> ${{ number_format($user->wallet->deposit_amount, 2) }}</p>
                    <p><strong>Withdrawal Amount:</strong> ${{ number_format($user->wallet->withdrawal_amount, 2) }}
                    </p>
                    <p><strong>Profit Amount:</strong> ${{ number_format($user->wallet->profit_amount, 2) }}</p>
                    <p><strong>Bonus Amount:</strong> ${{ number_format($user->wallet->bonus_amount, 2) }}</p>
                    <p><strong>Referral Amount:</strong> ${{ number_format($user->wallet->referral_amount, 2) }}</p>
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
               <p><strong>Total Referrals:</strong> {{ $referrals->count() }}</p>

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
                    $depositPending = $user->deposits->where('status', 'pending')->count();
                    $depositApproved = $user->deposits->where('status', 'approved')->count();
                    $depositRejected = $user->deposits->where('status', 'rejected')->count();
                @endphp
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
                    $withdrawalPending = $user->withdrawals->where('status', 'pending')->count();
                    $withdrawalApproved = $user->withdrawals->where('status', 'approved')->count();
                    $withdrawalRejected = $user->withdrawals->where('status', 'rejected')->count();
                @endphp
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




@include('admin.footer')
