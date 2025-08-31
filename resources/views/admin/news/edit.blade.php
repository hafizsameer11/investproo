@include('admin.head')
@include('admin.sidebar')
@include('admin.navbar')

<h4>Edit News</h4>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul></div>
@endif

<form action="{{ route('news.update', $news->id) }}" method="POST" class="card p-3">
    @csrf @method('PUT')

    <div class="mb-3">
        <label class="form-label">Title *</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $news->title) }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Type</label>
         <select name="status" class="form-select" required>
           <option value="update">Update</option>
           <option value="info">Info</option>
           <option value="news">News</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Status *</label>
        <select name="status" class="form-select" required>
            @foreach($statuses as $s)
                <option value="{{ $s }}" @selected(old('status', $news->status)===$s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Content *</label>
        <textarea name="content" rows="8" class="form-control" required>{{ old('content', $news->content) }}</textarea>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('news.index') }}" class="btn btn-outline-secondary">Back</a>
        <button class="btn btn-primary">Update</button>
    </div>
</form>

@include('admin.footer')
