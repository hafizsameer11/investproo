@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')
<h4>Deposits</h4>

<div class="row">
    <div class="col-xl-12">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-row">
                            {{-- <div class="col-3 align-self-center">
                                                    <div class="round">
                                                        <i class="mdi mdi-eye"></i>
                                                    </div>
                                                </div> --}}
                            <div class="col-9 align-self-center ">
                                <div class="m-l-10">
                                    <h2 class="mt-0">{{ $total_deposits }}</h2>
                                    <h6 class="mb-0 text-muted">Total Deposits </h6>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="progress mt-3" style="height:3px;">
                                                <div class="progress-bar  bg-success" role="progressbar"
                                                    style="width: 35%;" aria-valuenow="35" aria-valuemin="0"
                                                    aria-valuemax="100"></div>
                                            </div> --}}
                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-row">
                            {{-- <div class="col-3 align-self-center">
                                                    <div class="round">
                                                        <i class="mdi mdi-account-multiple-plus"></i>
                                                    </div>
                                                </div> --}}
                            <div class="col-9 align-self-center ">
                                <div class="m-l-10">
                                    <h2 class="mt-0">{{ $active_deposits }}</h2>
                                    <h6 class="mb-0 text-muted">Active Deposdit </h6>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="progress mt-3" style="height:3px;">
                                                <div class="progress-bar bg-warning" role="progressbar"
                                                    style="width: 48%;" aria-valuenow="48" aria-valuemin="0"
                                                    aria-valuemax="100"></div>
                                            </div> --}}
                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="search-type-arrow"></div>
                        <div class="d-flex flex-row">
                            {{-- <div class="col-3 align-self-center">
                                                    <div class="round ">
                                                        <i class="mdi mdi-cart"></i>
                                                    </div>
                                                </div> --}}
                            <div class="col-9 align-self-center ">
                                <div class="m-l-10">
                                    <h2 class="mt-0">{{ $pending_deposits }}</h2>
                                    <h6 class="mb-0 text-muted">Pending Deposits </h6>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="progress mt-3" style="height:3px;">
                                                <div class="progress-bar bg-danger" role="progressbar"
                                                    style="width: 61%;" aria-valuenow="61" aria-valuemin="0"
                                                    aria-valuemax="100"></div>
                                            </div> --}}
                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->

        {{-- <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Every Day Revenue</h4>
                <p class="text-muted mb-4 font-14"></p>
                <div id="morris-bar-stacked" class="morris-chart"></div>
            </div><!--end card-body-->
        </div><!--end card--> --}}
    </div><!--end col-->

    <!--end col-->
</div><!--end row-->

<div class="row mt-4">
    <div class="col-lg-12">
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Sr no</th>
                    <th>User Name</th>
                    <th>Plan Name</th>
                    <th>Amount</th>
                    <th>Deposit Pic</th>
                    <th>Deposit Chain</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($all_deposits as $index => $deposit)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $deposit->user->name ?? 'N/A' }}</td>
                        {{-- <td>{{ $deposit->investmentPlan->plan_name ?? 'N/A' }}</td> --}}
                        <td>${{ number_format($deposit->amount, 2) }}</td>
                        <td>
                            @if ($deposit->deposit_picture)
                                <img src="{{ asset($deposit->deposit_picture) }}" width="80" alt="Deposit Pic">
                            @else
                                No image
                            @endif
                        </td>
                        <td>{{ $deposit->chain->type ?? 'N/A' }}</td>
                        <td class="d-flex gap-2">
                            <!-- View Button -->
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                data-bs-target="#viewDepositModal{{ $deposit->id }}">View</button>
                            <!-- Edit Chain -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                data-bs-target="#editChainModal{{ $deposit->id }}">Edit Chain</button>
                            @if ($deposit->status === 'active')
                                <span class="btn btn-success btn-sm">Approved</span>
                            @else
                                <a href="{{ route('deposits.verify', $deposit->id) }}"
                                    class="btn btn-warning btn-sm">Unverified</a>
                            @endif
                            <form action="{{ route('deposits.destroy', $deposit->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Decline</button>
                            </form>
                        </td>
                    </tr>

                    <!-- View Modal -->
                    <div class="modal fade" id="viewDepositModal{{ $deposit->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Deposit Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>User:</strong> {{ $deposit->user->name }}</p>
                                    <p><strong>Plan:</strong> {{ $deposit->investmentPlan->plan_name }}</p>
                                    <p><strong>Amount:</strong> ${{ $deposit->amount }}</p>
                                    <p><strong>Deposit Address:</strong> {{ $deposit->chain->address ?? 'N/A' }}</p>
                                    <p><strong>Status:</strong> {{ ucfirst($deposit->status) }}</p>
                                    <p><strong>Date:</strong> {{ $deposit->deposit_date }}</p>
                                    @if ($deposit->deposit_picture)
                                        <img src="{{ asset($deposit->deposit_picture) }}" class="img-fluid">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Chain Modal -->
                    <div class="modal fade" id="editChainModal{{ $deposit->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('deposits.updateChain', $deposit->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Deposit Chain</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="chain_id" class="form-label">Chain</label>
                                            <select name="chain_id" class="form-select" required>
                                                @foreach ($chains as $chain) 
                                                    <option value="{{ $chain->id }}"
                                                        {{ $deposit->chain_id == $chain->id ? 'selected' : '' }}>
                                                        {{ $chain->type }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Update Chain</button>
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@include('admin.footer')
