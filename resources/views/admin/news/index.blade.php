@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<h4>News</h4>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-xl-12">
        <div class="row">
            <div class="col-lg-3">
                <div class="card"><div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="col-9 align-self-center">
                            <div class="m-l-10">
                                <h2 class="mt-0">{{ $totalNews }}</h2>
                                <h6 class="mb-0 text-muted">Total News</h6>
                            </div>
                        </div>
                    </div>
                </div></div>
            </div>

            <div class="col-lg-3">
                <div class="card"><div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="col-9 align-self-center">
                            <div class="m-l-10">
                                <h2 class="mt-0">{{ $activeNews }}</h2>
                                <h6 class="mb-0 text-muted">Active</h6>
                            </div>
                        </div>
                    </div>
                </div></div>
            </div>

            <div class="col-lg-3">
                <div class="card"><div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="col-9 align-self-center">
                            <div class="m-l-10">
                                <h2 class="mt-0">{{ $draftNews }}</h2>
                                <h6 class="mb-0 text-muted">Draft</h6>
                            </div>
                        </div>
                    </div>
                </div></div>
            </div>

            <div class="col-lg-3">
                <div class="card"><div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="col-9 align-self-center">
                            <div class="m-l-10">
                                <h2 class="mt-0">{{ $archivedNews }}</h2>
                                <h6 class="mb-0 text-muted">Archived</h6>
                            </div>
                        </div>
                    </div>
                </div></div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <form method="GET" class="row g-2 align-items-end" style="gap:10px">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Title or content">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All</option>
                        @foreach($allTypes as $t)
                            <option value="{{ $t }}" @selected($type===$t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['active','draft','archived'] as $s)
                            <option value="{{ $s }}" @selected($status===$s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

            <a href="{{ route('admin.news.create') }}" class="btn btn-success">+ Create News</a>
        </div>

        <div class="row mt-2">
            <div class="col-lg-12 col-md-12">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($news as $i => $item)
                            <tr>
                                <td>{{ $news->firstItem() + $i }}</td>
                                <td>{{ $item->title }}</td>
                                <td><span class="badge bg-info text-dark">{{ $item->getTypeBadgeText() }}</span></td>
                                <td>
                                    @if ($item->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($item->status === 'draft')
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    @else
                                        <span class="badge bg-secondary">Archived</span>
                                    @endif
                                </td>
                                <td>{{ $item->createdBy->name ?? 'N/A' }}</td>
                                <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                        data-bs-target="#viewNewsModal{{ $item->id }}">View</button>

                                    <a href="{{ route('admin.news.edit', $item->id) }}" class="btn btn-sm btn-primary">Edit</a>

                                    <form action="{{ route('admin.news.destroy', $item->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Delete this news?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>

                                    {{-- Quick status buttons --}}
                                    @if ($item->status !== 'active')
                                        <form action="{{ route('admin.news.status', $item->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                        </form>
                                    @endif
                                    @if ($item->status !== 'archived')
                                        <form action="{{ route('admin.news.status', $item->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="archived">
                                            <button type="submit" class="btn btn-sm btn-secondary">Archive</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            {{-- VIEW MODAL --}}
                            <div class="modal fade" id="viewNewsModal{{ $item->id }}" tabindex="-1"
                                aria-labelledby="viewNewsModalLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">News Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            {{-- You can also load via route if you prefer: iframe to admin.news.show --}}
                                            <h4 class="mb-1">{{ $item->title }}</h4>
                                            <p class="text-muted mb-2">
                                                <strong>Type:</strong> {{ ucfirst($item->type ?? 'N/A') }} |
                                                <strong>Status:</strong> {{ ucfirst($item->status) }} |
                                                <strong>By:</strong> {{ $item->createdBy->name ?? 'N/A' }} |
                                                <strong>Posted:</strong> {{ $item->getRelativeTime() }}
                                            </p>
                                            <hr>
                                            <div class="content">
                                                {!! nl2br(e($item->content)) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr><td colspan="7" class="text-center">No news found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $news->links() }}
            </div>
        </div>
    </div>
</div>

@include('admin.footer')
