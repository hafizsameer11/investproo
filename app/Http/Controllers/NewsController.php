<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
     public function index(Request $request)
    {
        // Stats
        $totalNews    = News::count();
        $activeNews   = News::where('status', 'active')->count();
        $inactiveNews = News::where('status', 'inactive')->count();

        // Filters
        $q      = $request->string('q')->toString();
        $type   = $request->string('type')->toString();
        $status = $request->string('status')->toString(); // expect '' | 'active' | 'inactive'

        $news = News::with('createdBy')
            ->when($q, function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                      ->orWhere('content', 'like', "%{$q}%");
                });
            })
            ->when($type, fn ($qb) => $qb->where('type', $type))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($qb) => $qb->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // For filter selects
        $allTypes = News::query()->select('type')->distinct()->pluck('type')->filter()->values();

        return view('admin.news.index', compact(
            'news', 'totalNews', 'activeNews', 'inactiveNews', 'allTypes', 'q', 'type', 'status'
        ));
    }

    public function create()
    {
        $statuses = ['active', 'inactive'];
        return view('admin.news.create', compact('statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'type'    => 'nullable|string|max:50',
            'status'  => 'required|string|in:active,inactive',
        ]);

        $data['created_by'] = Auth::id();
        News::create($data);

        return redirect()->route('news.index')->with('success', 'News created successfully.');
    }

    public function edit(News $news)
    {
        $statuses = ['active', 'inactive'];
        return view('admin.news.edit', compact('news', 'statuses'));
    }

    public function update(Request $request, News $news)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'type'    => 'nullable|string|max:50',
            'status'  => 'required|string|in:active,inactive',
        ]);

        $news->update($data);

        return redirect()->route('news.index')->with('success', 'News updated successfully.');
    }

    public function destroy(News $news)
    {
        $news->delete();
        return redirect()->route('news.index')->with('success', 'News deleted successfully.');
    }

    // For modal/view-only
    public function show(News $news)
    {
        return view('admin.news.show', compact('news'));
    }

    // Quick status toggle/update
    public function updateStatus(Request $request, News $news)
    {
        $data = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $news->update(['status' => $data['status']]);

        return back()->with('success', 'Status updated.');
    }
}
