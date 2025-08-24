@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<h4>Transactions</h4>

<div class="row mt-4">
    <div class="col-lg-12 col-md-12">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Sr No</th>
                    <th>User Name</th>
                    <th>Deposit Amount</th>
                    <th>Withdrawal Amount</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $index => $transaction)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                        <td>
                            @if($transaction->deposit)
                                ${{ number_format($transaction->deposit->amount, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($transaction->withdrawal)
                                ${{ number_format($transaction->withdrawal->amount, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#viewTransactionModal{{ $transaction->id }}">
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
@foreach ($transactions as $transaction)
    <div class="modal fade" id="viewTransactionModal{{ $transaction->id }}" tabindex="-1"
         aria-labelledby="viewTransactionModalLabel{{ $transaction->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>User:</strong> {{ $transaction->user->name ?? 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $transaction->user->email ?? 'N/A' }}</p>

                    @if($transaction->deposit)
                        <p><strong>Type:</strong> Deposit</p>
                        <p><strong>Amount:</strong> ${{ number_format($transaction->deposit->amount, 2) }}</p>
                    @elseif($transaction->withdrawal)
                        <p><strong>Type:</strong> Withdrawal</p>
                        <p><strong>Amount:</strong> ${{ number_format($transaction->withdrawal->amount, 2) }}</p>
                    @else
                        <p><strong>Type:</strong> Unknown</p>
                    @endif

                    <p><strong>Date:</strong> {{ $transaction->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
@endforeach

@include('admin.footer')
