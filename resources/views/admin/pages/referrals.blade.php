@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<h4>Referral Management</h4>

<div class="row mt-4">
    <div class="col-lg-12 col-md-12">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Sr No</th>
                    <th>User</th>
                    <th>Referral Code</th>
                    <th>Total Referrals</th>
                    <th>Per User Bonus</th>
                    <th>Bonus Amount</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($referrals as $index => $referral)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $referral->user->name ?? 'N/A' }}</td>
                        <td>{{ $referral->referral_code }}</td>
                        <td>{{ $referral->total_referrals }}</td>
                        <td>{{ $referral->per_user_referral }}</td>
                        <td>${{ number_format($referral->referral_bonus_amount, 2) }}</td>
                        <td>{{ $referral->created_at->format('Y-m-d') }}</td>
                        <td>
                            <!-- You can add Edit/Delete modals here if needed -->
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#viewReferralModal{{ $referral->id }}">
                                View
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- View Modals -->
@foreach ($referrals as $referral)
    <div class="modal fade" id="viewReferralModal{{ $referral->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Referral Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>User:</strong> {{ $referral->user->name ?? 'N/A' }}</p>
                    <p><strong>Referral Code:</strong> {{ $referral->referral_code }}</p>
                    <p><strong>Total Referrals:</strong> {{ $referral->total_referrals }}</p>
                    <p><strong>Per User Bonus:</strong> {{ $referral->per_user_referral }}</p>
                    <p><strong>Bonus Amount:</strong> ${{ number_format($referral->referral_bonus_amount, 2) }}</p>
                    <p><strong>Created At:</strong> {{ $referral->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
@endforeach

@include('admin.footer')
