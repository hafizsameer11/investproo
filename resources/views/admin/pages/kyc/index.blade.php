@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<h4>KYC Documents</h4>
<div class="row">
    <div class="col-xl-12">
        <div class="row">
            <div class="col-lg-3">
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
                                    <h2 class="mt-0">{{ $totalDocuments }}</h2>
                                    <h6 class="mb-0 text-muted">Total Documents </h6>
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

            <div class="col-lg-3">
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
                                    <h2 class="mt-0">{{ $approvedDocuments }}</h2>
                                    <h6 class="mb-0 text-muted">Approved Documents</h6>
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

            <div class="col-lg-3">
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
                                    <h2 class="mt-0">{{ $pendingDocuments }}</h2>
                                    <h6 class="mb-0 text-muted">Pending Documents</h6>
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
            <div class="col-lg-3">
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
                                    <h2 class="mt-0">{{ $rejectedDocuments }}</h2>
                                    <h6 class="mb-0 text-muted">Rejected Documents</h6>
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
    <div class="col-lg-12 col-md-12">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Sr No</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Document Type</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documents as $index => $document)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $document->user->name ?? 'N/A' }}</td>
                        <td>{{ $document->user->email ?? 'N/A' }}</td>
                        <td>{{ ucfirst($document->document_type) }}</td>
                        <td>
                            @if ($document->status == 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($document->status == 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>
                        <td>{{ $document->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <!-- View Button -->
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                data-bs-target="#viewDocumentModal{{ $document->id }}">
                                View
                            </button>


                            <!-- Verify Button -->
                            @if ($document->status != 'verified' && $document->status == 'pending')
                                <form action="{{ route('kyc.review', $document->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        Approved
                                    </button>
                                </form>
                            @endif
                            <!-- Verify Button -->
                            @if ($document->status != 'rejected' && $document->status == 'pending')
                                <form action="{{ route('kyc.review', $document->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Rejected
                                    </button>
                                </form>
                            @endif

                            <!-- Delete Button -->
                            {{-- <form action="" method="POST" class="d-inline"
                                onsubmit="return confirm('Are you sure you want to delete this document?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </form> --}}
                        </td>
                    </tr>

                    <!-- ================== VIEW MODAL ================== -->
                    <div class="modal fade" id="viewDocumentModal{{ $document->id }}" tabindex="-1"
                        aria-labelledby="viewDocumentModalLabel{{ $document->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">KYC Document Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">

                                    <p><strong>User:</strong> {{ $document->user->name ?? 'N/A' }}</p>
                                    <p><strong>Email:</strong> {{ $document->user->email ?? 'N/A' }}</p>
                                    <p><strong>Document Type:</strong> {{ ucfirst($document->document_type) }}</p>
                                    <p><strong>Status:</strong> {{ ucfirst($document->status) }}</p>
                                    <p><strong>Submitted At:</strong> {{ $document->created_at->format('Y-m-d H:i') }}
                                    </p>
                                    {{-- <h1>{{  storage_path('app/private/' . $document->file_path)  }}</h1> --}}
                                    @if ($document->file_path)
                                        <h6 class="mt-3">Document Preview:</h6>
                                       <iframe src="{{ route('document.view', $document->id) }}" width="50%" height="150px"></iframe>


                                        <div class="mt-3">
                                            <a href="{{ route('kyc.adminDownload', $document->id) }}" target="_blank" download
                                                class="btn btn-success">
                                                Download Document
                                            </a>
                                        </div>
                                    @else
                                        <p>No file uploaded.</p>
                                    @endif


                                </div>
                            </div>
                        </div>
                    </div>

                   
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@include('admin.footer')
