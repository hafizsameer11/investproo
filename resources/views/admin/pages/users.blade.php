@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')
<h4>Users</h4>

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
                                    <h2 class="mt-0">{{ $total_users }}</h2>
                                    <h6 class="mb-0 text-muted">Total Users </h6>
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
                                    <h2 class="mt-0">{{ $active_users }}</h2>
                                    <h6 class="mb-0 text-muted">Active Users </h6>
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
                                    <h2 class="mt-0">{{ $inactive_user }}</h2>
                                    <h6 class="mb-0 text-muted">KYC unverified </h6>
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

<div class="row">
    <div class="col-lg-12 col-md-12">

        <table class="table table-striped table-hover table-bordered">
            <thead>
                <tr class="">
                    <th scope="col">Sr no</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">User Code</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($all_users as $user)
                    <tr>

                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->user_code }}</td>
                        <td class="d-flex gap-1">
                    <a href="{{ route('user.detail', $user->id) }}" class="btn btn-primary btn-sm">
                        View
                    </a>  
                            @if ($user->status === 'active')
                            <span class="btn btn-success btn-sm mr-2">Verified</span>
                        @else
                            <a href="{{ route('kyc', $user->id) }}" class="btn btn-warning btn-sm mr-2">Unverified</a>
                        @endif
                            <form action="{{ route('destroy.user', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure?')"
                                style="display: inline;">
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
<!-- Modal -->
 {{-- @foreach ($all_users as $user)
<div class="modal fade" id="userModal{{ $user->id }}" tabindex="-1" aria-labelledby="userModalLabel{{ $user->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel{{ $user->id }}">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <p><strong>Name:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Status:</strong> {{ $user->status  }}</p>
                    <!-- Add more fields as needed -->
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            
            </div>
        </div>
    </div>
@endforeach --}}
@include('admin.footer')
