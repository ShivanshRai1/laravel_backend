<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BlogPost;
use App\Models\FinancialData;

class ContentController extends Controller
{
    /**
     * Get all articles/blog posts
     */
    public function getArticles(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $status = $request->get('status');
            
            $query = BlogPost::query();
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $articles = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $articles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch articles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new article
     */
    public function createArticle(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'status' => 'required|in:draft,pending,published,archived'
            ]);

            $article = BlogPost::create([
                'title' => $request->title,
                'content' => $request->content,
                'status' => $request->status,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Article created successfully',
                'data' => $article
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing article
     */
    public function updateArticle(Request $request, $id): JsonResponse
    {
        try {
            $article = BlogPost::findOrFail($id);
            
            $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'status' => 'sometimes|required|in:draft,pending,published,archived'
            ]);

            $article->update($request->only(['title', 'content', 'status']));

            return response()->json([
                'success' => true,
                'message' => 'Article updated successfully',
                'data' => $article
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an article
     */
    public function deleteArticle($id): JsonResponse
    {
        try {
            $article = BlogPost::findOrFail($id);
            $article->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete article',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}