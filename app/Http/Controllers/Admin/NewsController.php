<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::with('createdBy')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalNews = News::count();
        $activeNews = News::where('status', 'active')->count();
        $inactiveNews = News::where('status', 'inactive')->count();

        return view('admin.pages.news', compact('news', 'totalNews', 'activeNews', 'inactiveNews'));
    }

    public function create()
    {
        return view('admin.pages.news-create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:news,update,info',
            'status' => 'sometimes|in:active,inactive'
        ]);

        News::create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'status' => $request->status ?? 'active',
            'created_by' => Auth::id()
        ]);

        return redirect()->route('news.index')->with('success', 'News created successfully');
    }

    public function edit($id)
    {
        $news = News::findOrFail($id);
        return view('admin.pages.news-edit', compact('news'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:news,update,info',
            'status' => 'sometimes|in:active,inactive'
        ]);

        $news = News::findOrFail($id);
        $news->update($request->only(['title', 'content', 'type', 'status']));

        return redirect()->route('news.index')->with('success', 'News updated successfully');
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete();

        return redirect()->route('news.index')->with('success', 'News deleted successfully');
    }
}
