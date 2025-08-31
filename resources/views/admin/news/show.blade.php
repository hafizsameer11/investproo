<div class="p-3">
    <h4 class="mb-1">{{ $news->title }}</h4>
    <p class="text-muted mb-2">
        <strong>Type:</strong> {{ ucfirst($news->type ?? 'N/A') }} |
        <strong>Status:</strong> {{ ucfirst($news->status) }} |
        <strong>By:</strong> {{ $news->createdBy->name ?? 'N/A' }} |
        <strong>Posted:</strong> {{ $news->getRelativeTime() }}
    </p>
    <hr>
    <div class="content">
        {!! nl2br(e($news->content)) !!}
    </div>
</div>
