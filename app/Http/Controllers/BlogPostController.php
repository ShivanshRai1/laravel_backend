<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\BlogPost;
use App\Models\BlogPostApproval;
use App\Notifications\BlogPostStatusChanged;

class BlogPostController extends Controller
{
    /**
     * Get all pending blog posts for approval
     */
    public function getPending(Request $request): JsonResponse
    {
        try {
            $query = BlogPost::with(['author'])
                ->pending()
                ->orderBy('created_at', 'desc');

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('excerpt', 'LIKE', "%{$search}%")
                      ->orWhere('content', 'LIKE', "%{$search}%");
                });
            }

            if ($request->has('author_id')) {
                $query->where('user_id', $request->author_id);
            }

            $posts = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a blog post
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'admin_notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $post = BlogPost::findOrFail($id);
            
            if ($post->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending posts can be approved'
                ], 400);
            }

            // Update post status
            $post->update([
                'status' => 'published',
                'published_at' => now(),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'admin_notes' => $request->admin_notes,
                'rejection_reason' => null,
            ]);

            // Create approval record
            BlogPostApproval::create([
                'blog_post_id' => $post->id,
                'admin_id' => $request->user()->id,
                'action' => 'approved',
                'admin_notes' => $request->admin_notes,
            ]);

            // Send notification to author
            if ($post->author) {
                $post->author->notify(new BlogPostStatusChanged(
                    $post,
                    'approved',
                    null,
                    $request->admin_notes,
                    $request->user()->name
                ));
            }

            return response()->json([
                'success' => true,
                'message' => 'Blog post approved and published',
                'data' => $post->load(['author', 'reviewer'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a blog post
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'required|string',
                'admin_notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $post = BlogPost::findOrFail($id);
            
            if ($post->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending posts can be rejected'
                ], 400);
            }

            // Update post status
            $post->update([
                'status' => 'draft',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => $request->reason,
                'admin_notes' => $request->admin_notes,
                'published_at' => null,
            ]);

            // Create approval record
            BlogPostApproval::create([
                'blog_post_id' => $post->id,
                'admin_id' => $request->user()->id,
                'action' => 'rejected',
                'reason' => $request->reason,
                'admin_notes' => $request->admin_notes,
            ]);

            // Send notification to author
            if ($post->author) {
                $post->author->notify(new BlogPostStatusChanged(
                    $post,
                    'rejected',
                    $request->reason,
                    $request->admin_notes,
                    $request->user()->name
                ));
            }

            return response()->json([
                'success' => true,
                'message' => 'Blog post rejected',
                'data' => $post->load(['author', 'reviewer'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request changes on a blog post
     */
    public function requestChanges(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'comment' => 'required|string',
                'admin_notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $post = BlogPost::findOrFail($id);
            
            if ($post->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending posts can have changes requested'
                ], 400);
            }

            // Update post status
            $post->update([
                'status' => 'draft',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => $request->comment,
                'admin_notes' => $request->admin_notes,
                'published_at' => null,
            ]);

            // Create approval record
            BlogPostApproval::create([
                'blog_post_id' => $post->id,
                'admin_id' => $request->user()->id,
                'action' => 'changes_requested',
                'reason' => $request->comment,
                'admin_notes' => $request->admin_notes,
            ]);

            // Send notification to author
            if ($post->author) {
                $post->author->notify(new BlogPostStatusChanged(
                    $post,
                    'changes_requested',
                    $request->comment,
                    $request->admin_notes,
                    $request->user()->name
                ));
            }

            return response()->json([
                'success' => true,
                'message' => 'Changes requested',
                'data' => $post->load(['author', 'reviewer'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get approval history for all posts or a specific post
     */
    public function getApprovalHistory(Request $request, $postId = null): JsonResponse
    {
        try {
            $query = BlogPostApproval::with(['blogPost', 'admin'])
                ->orderBy('created_at', 'desc');

            if ($postId) {
                $query->where('blog_post_id', $postId);
            }

            if ($request->has('action')) {
                $query->where('action', $request->action);
            }

            if ($request->has('admin_id')) {
                $query->where('admin_id', $request->admin_id);
            }

            $history = $query->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve posts
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'post_ids' => 'required|array',
                'post_ids.*' => 'exists:blog_posts,id',
                'admin_notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $approved = 0;
            foreach ($request->post_ids as $postId) {
                $post = BlogPost::find($postId);
                
                if ($post && $post->status === 'pending') {
                    $post->update([
                        'status' => 'published',
                        'published_at' => now(),
                        'reviewed_by' => $request->user()->id,
                        'reviewed_at' => now(),
                        'admin_notes' => $request->admin_notes,
                    ]);

                    BlogPostApproval::create([
                        'blog_post_id' => $post->id,
                        'admin_id' => $request->user()->id,
                        'action' => 'approved',
                        'admin_notes' => $request->admin_notes,
                    ]);

                    $approved++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "$approved posts approved successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
