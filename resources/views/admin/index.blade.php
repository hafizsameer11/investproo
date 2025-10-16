@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<div class="container-fluid">
    <div class="row g-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Users</h6>
                            <div class="display-6">{{ $total_users }}</div>
                        </div>
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="mb-1">Active Users</h6>
                    <div class="display-6">{{ $active_users }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="mb-1">Total Deposits</h6>
                    <div class="display-6">${{ number_format($total_deposit, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="mb-1">Total Withdrawals</h6>
                    <div class="display-6">${{ number_format($total_withdrawal_amount, 2) }}</div>
                    <small>Count: {{ $total_withdrawal }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-1"><i class="fas fa-calendar-day"></i> Today's Deposits</h6>
                    <div class="d-flex justify-content-between">
                        <div>Amount</div>
                        <div class="fw-bold">${{ number_format($todayDepositsAmount, 2) }}</div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>Count</div>
                        <div class="fw-bold">{{ $todayDepositsCount }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-1"><i class="fas fa-calendar-day"></i> Today's Withdrawals</h6>
                    <div class="d-flex justify-content-between">
                        <div>Amount</div>
                        <div class="fw-bold">${{ number_format($todayWithdrawalsAmount, 2) }}</div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>Count</div>
                        <div class="fw-bold">{{ $todayWithdrawalsCount }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-1">Deposits</h6>
                    <div class="d-flex justify-content-between"><span>Approved</span><span class="fw-bold">{{ $approved_deposits }}</span></div>
                    <div class="d-flex justify-content-between"><span>Pending</span><span class="fw-bold">{{ $pending_deposits }}</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-1">Withdrawals</h6>
                    <div class="d-flex justify-content-between"><span>Approved</span><span class="fw-bold">{{ $active_withdrawal }}</span></div>
                    <div class="d-flex justify-content-between"><span>Pending</span><span class="fw-bold">{{ $pending_withdrawal }}</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">Recent Users</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($all_users as $u)
                            <tr>
                                <td>{{ $u->id }}</td>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $u->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($u->status) }}</span>
                                </td>
                                <td>{{ $u->created_at?->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('admin.footer')
@include('admin.head')



@include('admin.sidebar')




@include('admin.navbar')




<div class="row">
    <div class="col-xl-8">
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
                                    <h2 class="mt-0">{{ $total_withdrawal }}</h2>
                                    <h6 class="mb-0 text-muted">Withdrawal </h6>
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

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <h3> $ {{ $total_deposit }}</h3>
                        <h6 class="text-lightdark">Total Deposit</h6>
                        <span class="text-muted"> <small>Current Month</small></span>
                    </div>
                    {{-- <div class="col-4 text-center">
                                            <h5><i class="mdi mdi-airplane-takeoff mr-2 text-danger font-20"></i> 80%
                                            </h5>
                                            <h6 class="text-lightdark">Export</h6>
                                            <span class="text-muted"> <small>2018 to 2019</small></span>
                                        </div>
                </div> --}}
                </div><!--end card-body-->
                {{-- <div class="card-body p-0 mb-n5">
                <div class="mb-0 area-chart-map" id="morris-area-chart"></div>
            </div> --}}
                {{-- <div class="card mb-0 bg-map">
                <div class="card-body ">
                    <div id="world-map-markers" class="dash-map"></div>
                </div>
            </div><!--end card--> --}}
            </div><!--end card-->
        </div><!--end col-->
    </div><!--end row-->


    {{-- <div class="row">
    <div class="col-xl-4 ">
        <div class="card">
            <div class="card-body">
                <h5 class="header-title mt-0  mb-3">Summary</h5>
                <div id="donut-example"></div>
                <ul class="list-unstyled text-center text-muted mt-2 mb-0">
                    <li class="list-inline-item font-13"><i
                            class="mdi mdi-album font-16 text-secondary mr-2"></i>Laptops</li>
                    <li class="list-inline-item font-13"><i class="mdi mdi-album font-16 text-info mr-2"></i>Iphones
                    </li>
                    <li class="list-inline-item font-13"><i class="mdi mdi-album font-16 text-light mr-2"></i>Tablets
                    </li>
                </ul>
            </div><!--end card-body-->
        </div><!--end card-->
    </div><!--end col-->

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="header-title mt-0 mb-0">Weather</h5>
                <div class="row">
                    <div class="col-lg-12 col-sm-12 text-center align-self-center">
                        <h6 class="">San Francisco, California</h6>
                        <p class="text-muted">SUNDAY <span>25<sup>th</sup> Jan</span></p>
                        <canvas id="clear-day" width="50" height="50"></canvas>
                        <h5 class="mt-0 "><b>32°</b></h5>
                        <p class="text-muted  font-12">Clear Day</p>
                        <p class="text-muted">There are many variations of passages of
                            Lorem Ipsum available, but the majority have suffered alteration.
                        </p>
                    </div>
                </div>
                <div class="row ">
                    <div class="col-4  text-center align-self-center">
                        <h6 class="text-muted mt-0 font-14">MON</h6>
                        <canvas id="rain" width="28" height="18"></canvas>
                        <h6 class="text-muted font-14 mb-0">28°<i class="wi-degrees"></i></h6>
                    </div>
                    <div class="col-4  text-center align-self-center">
                        <h6 class="text-muted mt-0 font-14">TUE</h6>
                        <canvas id="wind" width="35" height="18"></canvas>
                        <h6 class="text-muted font-14 mb-0">32°<i class="wi-degrees"></i></h6>
                    </div>
                    <div class="col-4  text-center align-self-center">
                        <h6 class="text-muted mt-0 font-14">WEN</h6>
                        <canvas id="snow" width="35" height="18"></canvas>
                        <h6 class="text-muted font-14 mb-0">10°<i class="wi-degrees"></i></h6>
                    </div>
                </div>
            </div><!--end card-body-->
        </div><!--end card-->
    </div><!--end col-->


    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="header-title mt-0 mb-3">Calendar</h5>
                <div id="v-cal">
                    <div class="vcal-header">
                        <button class="vcal-btn" data-calendar-toggle="previous">
                            <svg height="24" version="1.1" viewbox="0 0 24 24" width="24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z">
                                </path>
                            </svg>
                        </button>

                        <div class="vcal-header__label" data-calendar-label="month">
                            March 2017
                        </div>


                        <button class="vcal-btn" data-calendar-toggle="next">
                            <svg height="24" version="1.1" viewbox="0 0 24 24" width="24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z">
                                </path>
                            </svg>
                        </button>
                    </div>
                    <div class="vcal-week">
                        <span>Mon</span>
                        <span>Tue</span>
                        <span>Wed</span>
                        <span>Thu</span>
                        <span>Fri</span>
                        <span>Sat</span>
                        <span>Sun</span>
                    </div>
                    <div class="vcal-body" data-calendar-area="month"></div>
                </div>
            </div><!--end card-body-->
        </div><!--end card-->
    </div><!--end col-->
</div> <!-- end row --> --}}

    {{-- <div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body new-user">
                <h5 class="header-title mb-4 mt-0">New Users</h5>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="border-top-0">Users</th>
                                <th class="border-top-0">Name</th>
                                <th class="border-top-0">Country</th>
                                <th class="border-top-0">Reviews</th>
                                <th class="border-top-0">Socials</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <img class="rounded-circle" src="assets/images/users/avatar-2.jpg" alt="user"
                                        width="30">
                                </td>
                                <td>
                                    <a href="javascript:void(0);">Ruby T. Curd</a>
                                </td>
                                <td>
                                    <img src="assets/images/flags/us_flag.jpg" alt="" class="img-flag">
                                </td>
                                <td>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star-half text-warning"></i>
                                    <i class="mdi mdi-star-outline text-warning"></i>
                                </td>
                                <td>
                                    <ul class="list-unstyled list-inline mb-0">
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-facebook text-primary"></i></a></li>
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-linkedin text-danger"></i></a></li>
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-twitter-alt text-info"></i></a></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img class="rounded-circle" src="assets/images/users/avatar-3.jpg" alt="user"
                                        width="30">
                                </td>
                                <td>
                                    <a href="javascript:void(0);">William A. Johnson</a>
                                </td>
                                <td>
                                    <img src="assets/images/flags/french_flag.jpg" alt="" class="img-flag">
                                </td>
                                <td>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star-half text-warning"></i>
                                    <i class="mdi mdi-star-outline text-warning"></i>
                                </td>
                                <td>
                                    <ul class="list-unstyled list-inline mb-0">
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-facebook text-primary"></i></a></li>
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-linkedin text-danger"></i></a></li>
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-twitter-alt text-info"></i></a></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img class="rounded-circle" src="assets/images/users/avatar-4.jpg" alt="user"
                                        width="30">
                                </td>
                                <td>
                                    <a href="javascript:void(0);">Bobby M. Gray</a>
                                </td>
                                <td>
                                    <img src="assets/images/flags/spain_flag.jpg" alt="" class="img-flag">
                                </td>
                                <td>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star-half text-warning"></i>
                                    <i class="mdi mdi-star-outline text-warning"></i>
                                </td>
                                <td>
                                    <ul class="list-unstyled list-inline mb-0">
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-facebook text-primary"></i></a></li>
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-linkedin text-danger"></i></a></li>
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-twitter-alt text-info"></i></a></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img class="rounded-circle" src="assets/images/users/avatar-5.jpg" alt="user"
                                        width="30">
                                </td>
                                <td>
                                    <a href="javascript:void(0);">Robert N. Carlile</a>
                                </td>
                                <td>
                                    <img src="assets/images/flags/russia_flag.jpg" alt="" class="img-flag">
                                </td>
                                <td>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star-half text-warning"></i>
                                    <i class="mdi mdi-star-outline text-warning"></i>
                                </td>
                                <td>
                                    <ul class="list-unstyled list-inline mb-0">
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-facebook text-primary"></i></a></li>
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-linkedin text-danger"></i></a></li>
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-twitter-alt text-info"></i></a></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img class="rounded-circle" src="assets/images/users/avatar-6.jpg" alt="user"
                                        width="30">
                                </td>
                                <td>
                                    <a href="javascript:void(0);">Ruby T. Curd</a>
                                </td>
                                <td>
                                    <img src="assets/images/flags/italy_flag.jpg" alt="" class="img-flag">
                                </td>
                                <td>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star-half text-warning"></i>
                                    <i class="mdi mdi-star-outline text-warning"></i>
                                </td>
                                <td>
                                    <ul class="list-unstyled list-inline mb-0">
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-facebook text-primary"></i></a></li>
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-linkedin text-danger"></i></a></li>
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-twitter-alt text-info"></i></a></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img class="rounded-circle" src="assets/images/users/avatar-2.jpg" alt="user"
                                        width="30">
                                </td>
                                <td>
                                    <a href="javascript:void(0);">Ruby T. Curd</a>
                                </td>
                                <td>
                                    <img src="assets/images/flags/us_flag.jpg" alt="" class="img-flag">
                                </td>
                                <td>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star text-warning"></i>
                                    <i class="mdi mdi-star-half text-warning"></i>
                                    <i class="mdi mdi-star-outline text-warning"></i>
                                </td>
                                <td>
                                    <ul class="list-unstyled list-inline mb-0">
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-facebook text-primary"></i></a></li>
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-linkedin text-danger"></i></a></li>
                                        <li class="list-inline-item"><a href="#"><i
                                                    class="ti-twitter-alt text-info"></i></a></li>
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div><!--end card-body-->
        </div><!--end card-->
    </div><!--end col-->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body new-user">
                <h5 class="header-title mb-4 mt-0">Order List</h5>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="border-top-0">Product</th>
                                <th class="border-top-0">Pro Name</th>
                                <th class="border-top-0">Country</th>
                                <th class="border-top-0">Order Date/Time</th>
                                <th class="border-top-0">Pcs.</th>
                                <th class="border-top-0">Amount ($)</th>
                                <th class="border-top-0">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <img class="" src="assets/images/products/pro1.png" alt="user">
                                </td>
                                <td>
                                    Chair
                                </td>
                                <td>
                                    <img src="assets/images/flags/us_flag.jpg" alt="" class="img-flag">
                                </td>
                                <td>3/09/2018 4:29 PM</td>
                                <td>2</td>
                                <td> $ 50</td>
                                <td>
                                    <span class="badge badge-boxed  badge-success">Shipped</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img class="" src="assets/images/products/pro2.png" alt="user">
                                </td>
                                <td>
                                    Mobile
                                </td>
                                <td>
                                    <img src="assets/images/flags/french_flag.jpg" alt="" class="img-flag">
                                </td>
                                <td>3/15/2018 1:09 PM</td>
                                <td>1</td>
                                <td> $ 70</td>
                                <td>
                                    <span class="badge badge-boxed  badge-danger">Delivered</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img class="" src="assets/images/products/pro3.png" alt="user">
                                </td>
                                <td>
                                    LED
                                </td>
                                <td>
                                    <img src="assets/images/flags/spain_flag.jpg" alt="" class="img-flag">
                                </td>
                                <td>3/18/2018 12:09 PM</td>
                                <td>3</td>
                                <td> $ 200</td>
                                <td>
                                    <span class="badge badge-boxed badge-warning">Pending</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img class="" src="assets/images/products/pro4.png" alt="user">
                                </td>
                                <td>
                                    Chair
                                </td>
                                <td>
                                    <img src="assets/images/flags/russia_flag.jpg" alt="" class="img-flag">
                                </td>
                                <td>3/27/2018 8:27 PM</td>
                                <td>2</td>
                                <td> $ 20</td>
                                <td>
                                    <span class="badge badge-boxed  badge-success">Shipped</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img class="" src="assets/images/products/pro2.png" alt="user">
                                </td>
                                <td>
                                    Mobile
                                </td>
                                <td>
                                    <img src="assets/images/flags/italy_flag.jpg" alt="" class="img-flag">
                                </td>
                                <td>4/01/2018 5:09 PM</td>
                                <td>1</td>
                                <td> $ 150</td>
                                <td>
                                    <span class="badge badge-boxed badge-warning">Pending</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img class="" src="assets/images/products/pro1.png" alt="user">
                                </td>
                                <td>
                                    Chair
                                </td>
                                <td>
                                    <img src="assets/images/flags/us_flag.jpg" alt="" class="img-flag">
                                </td>
                                <td>3/09/2018 4:29 PM</td>
                                <td>2</td>
                                <td> $ 50</td>
                                <td>
                                    <span class="badge badge-boxed  badge-success">Shipped</span>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div><!--end card-body-->
        </div><!--end card-->
    </div><!--end col-->
</div> <!--end row--> --}}

    <div class="row">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="col-9 align-self-center ">
                            <div class="m-l-10">
                                <h2 class="mt-0">{{ $active_withdrawal }}</h2>
                                <h6 class="mb-0 text-muted">Active Withdrawals</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="col-9 align-self-center ">
                            <div class="m-l-10">
                                <h2 class="mt-0">{{ $pending_withdrawal }}</h2>
                                <h6 class="mb-0 text-muted">Pending Withdrawals</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="col-9 align-self-center ">
                            <div class="m-l-10">
                                <h2 class="mt-0">{{ $approved_deposits }}</h2>
                                <h6 class="mb-0 text-muted">Approved Deposits</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="col-9 align-self-center ">
                            <div class="m-l-10">
                                <h2 class="mt-0">{{ $pending_deposits }}</h2>
                                <h6 class="mb-0 text-muted">Pending Deposits</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>







    @include('admin.footer')
