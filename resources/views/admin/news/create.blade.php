@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<h4>Create News</h4>

@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul></div>
@endif

<form action="{{ route('news.store') }}" method="POST" class="card p-3">
    @csrf
    <div class="mb-3">
        <label class="form-label">Title *</label>
        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Type</label>
        <input type="text" name="type" class="form-control" value="{{ old('type') }}" placeholder="e.g. general / announcement">
    </div>

    <div class="mb-3">
        <label class="form-label">Status *</label>
        <select name="status" class="form-select" required>
            @foreach($statuses as $s)
                <option value="{{ $s }}" @selected(old('status')===$s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Content *</label>
        <textarea name="content" rows="8" class="form-control" required>{{ old('content') }}</textarea>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('news.index') }}" class="btn btn-outline-secondary">Back</a>
        <button class="btn btn-success">Create</button>
    </div>
</form>

@include('admin.footer')
