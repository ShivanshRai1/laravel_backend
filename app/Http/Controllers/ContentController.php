<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\BlogPost;
use App\Models\FinancialData;

class ContentController extends Controller
{
    /**
     * Get published articles for public access (no authentication required)
     */
    public function getPublishedArticles(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            
            $articles = BlogPost::with('author')
                ->where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
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
     * Get all articles/blog posts (for admin/editor management)
     */
    public function getArticles(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $status = $request->get('status');
            
            $user = auth()->user();
            $query = BlogPost::with('author');
            
            // Admin and Editor can see all articles
            if (!in_array(strtolower($user->role), ['admin', 'editor'])) {
                // Regular users can only see published articles + their own drafts/pending
                $query->where(function($q) use ($user) {
                    $q->where('status', 'published')
                      ->orWhere('user_id', $user->id);
                });
            }
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $articles = $query->orderBy('created_at', 'desc')->paginate($perPage);
            
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
                'excerpt' => 'nullable|string',
                'meta_description' => 'nullable|string|max:160',
                'tags' => 'nullable|array',
                'status' => 'required|in:draft,pending,published,archived'
            ]);

            $user = auth()->user();
            
            // Regular users (non-admin/editor) must have posts set to 'pending' for approval
            $status = $request->status;
            if (!in_array(strtolower($user->role), ['admin', 'editor'])) {
                $status = 'pending';
            }

            $article = BlogPost::create([
                'title' => $request->title,
                'content' => $request->content,
                'excerpt' => $request->excerpt,
                'meta_description' => $request->meta_description,
                'tags' => $request->tags,
                'status' => $status,
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
                'excerpt' => 'sometimes|nullable|string',
                'meta_description' => 'sometimes|nullable|string|max:160',
                'tags' => 'sometimes|nullable|array',
                'status' => 'sometimes|required|in:draft,pending,published,archived'
            ]);

            $user = auth()->user();
            $updateData = $request->only(['title', 'content', 'excerpt', 'meta_description', 'tags', 'status']);
            
            // Regular users can only update their own posts
            if (!in_array(strtolower($user->role), ['admin', 'editor'])) {
                // Check ownership
                if ($article->user_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only edit your own posts'
                    ], 403);
                }
                
                // Force status to 'pending' for regular users (can't self-publish)
                if (isset($updateData['status'])) {
                    $updateData['status'] = 'pending';
                }
            }

            $article->update($updateData);

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
            
            $user = auth()->user();
            
            // Regular users can only delete their own posts
            if (!in_array(strtolower($user->role), ['admin', 'editor'])) {
                if ($article->user_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only delete your own posts'
                    ], 403);
                }
            }
            
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