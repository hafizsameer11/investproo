<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    /**
     * Get active news for users
     */
    public function index()
    {
        try {
            $news = News::getActiveNews(10);
            
            return ResponseHelper::success($news, 'News retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to retrieve news: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get news by type
     */
    public function getByType(Request $request, $type)
    {
        try {
            $validator = Validator::make(['type' => $type], [
                'type' => 'required|in:news,update,info'
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $news = News::getNewsByType($type, 10);
            
            return ResponseHelper::success($news, 'News retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to retrieve news: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a new news item (Admin only)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'type' => 'required|in:news,update,info',
                'status' => 'sometimes|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $news = News::create([
                'title' => $request->title,
                'content' => $request->content,
                'type' => $request->type,
                'status' => $request->status ?? 'active',
                'created_by' => Auth::id()
            ]);

            return ResponseHelper::success($news, 'News created successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to create news: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update a news item (Admin only)
     */
    public function update(Request $request, $id)
    {
        try {
            $news = News::find($id);
            if (!$news) {
                return ResponseHelper::error('News not found', 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
                'type' => 'sometimes|in:news,update,info',
                'status' => 'sometimes|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $news->update($request->only(['title', 'content', 'type', 'status']));

            return ResponseHelper::success($news, 'News updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to update news: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a news item (Admin only)
     */
    public function destroy($id)
    {
        try {
            $news = News::find($id);
            if (!$news) {
                return ResponseHelper::error('News not found', 404);
            }

            $news->delete();

            return ResponseHelper::success(null, 'News deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to delete news: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all news for admin panel
     */
    public function adminIndex()
    {
        try {
            $news = News::with('createdBy')
                ->orderBy('created_at', 'desc')
                ->get();

            return ResponseHelper::success($news, 'All news retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to retrieve news: ' . $e->getMessage(), 500);
        }
    }
}
