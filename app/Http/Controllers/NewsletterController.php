<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    /**
     * Display a listing of newsletters.
     */
    public function index()
    {
        try {
            $newsletters = Newsletter::with('creator:id,name,email')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $newsletters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch newsletters',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created newsletter.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'subject' => 'required|string|max:255',
                'content' => 'required|string',
                'status' => 'in:draft,scheduled',
                'scheduled_at' => 'nullable|date|after:now'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $newsletter = Newsletter::create([
                'title' => $request->title,
                'subject' => $request->subject,
                'content' => $request->content,
                'status' => $request->status ?? 'draft',
                'scheduled_at' => $request->scheduled_at,
                'created_by' => Auth::id()
            ]);

            $newsletter->load('creator:id,name,email');

            return response()->json([
                'success' => true,
                'message' => 'Newsletter created successfully',
                'data' => $newsletter
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create newsletter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified newsletter.
     */
    public function show(string $id)
    {
        try {
            $newsletter = Newsletter::with('creator:id,name,email')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $newsletter
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Newsletter not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified newsletter.
     */
    public function update(Request $request, string $id)
    {
        try {
            $newsletter = Newsletter::findOrFail($id);
            
            // Prevent updating sent newsletters
            if ($newsletter->status === 'sent') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update sent newsletters'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'subject' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'status' => 'sometimes|in:draft,scheduled',
                'scheduled_at' => 'nullable|date|after:now'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $newsletter->update($request->only([
                'title', 'subject', 'content', 'status', 'scheduled_at'
            ]));

            $newsletter->load('creator:id,name,email');

            return response()->json([
                'success' => true,
                'message' => 'Newsletter updated successfully',
                'data' => $newsletter
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update newsletter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified newsletter.
     */
    public function destroy(string $id)
    {
        try {
            $newsletter = Newsletter::findOrFail($id);
            
            // Prevent deleting sent newsletters
            if ($newsletter->status === 'sent') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete sent newsletters'
                ], 400);
            }

            $newsletter->delete();

            return response()->json([
                'success' => true,
                'message' => 'Newsletter deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete newsletter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send newsletter to all active subscribers.
     */
    public function send(string $id)
    {
        try {
            $newsletter = Newsletter::findOrFail($id);
            
            if ($newsletter->status === 'sent') {
                return response()->json([
                    'success' => false,
                    'message' => 'Newsletter has already been sent'
                ], 400);
            }

            $subscribers = Subscriber::active()->verified()->get();
            $sentCount = 0;

            foreach ($subscribers as $subscriber) {
                try {
                    // Send newsletter email
                    \Mail::to($subscriber->email)->send(new \App\Mail\NewsletterMail($newsletter, $subscriber->email));
                    $sentCount++;
                } catch (\Exception $e) {
                    // Log individual send failures
                    \Log::error("Failed to send newsletter to {$subscriber->email}: " . $e->getMessage());
                }
            }

            $newsletter->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sent_count' => $sentCount
            ]);

            return response()->json([
                'success' => true,
                'message' => "Newsletter sent to {$sentCount} subscribers",
                'data' => $newsletter
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send newsletter',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get newsletter statistics.
     */
    public function stats()
    {
        try {
            $totalNewsletters = Newsletter::count();
            $draftNewsletters = Newsletter::draft()->count();
            $sentNewsletters = Newsletter::sent()->count();
            $scheduledNewsletters = Newsletter::scheduled()->count();
            $totalSubscribers = Subscriber::active()->verified()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_newsletters' => $totalNewsletters,
                    'draft_newsletters' => $draftNewsletters,
                    'sent_newsletters' => $sentNewsletters,
                    'scheduled_newsletters' => $scheduledNewsletters,
                    'total_subscribers' => $totalSubscribers
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
