<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriberController extends Controller
{
    /**
     * Display a listing of subscribers.
     */
    public function index()
    {
        try {
            $subscribers = Subscriber::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $subscribers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscribers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created subscriber.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:subscribers',
                'name' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $subscriber = Subscriber::create([
                'email' => $request->email,
                'name' => $request->name,
                'status' => 'active',
                'verified_at' => now() // Auto-verify for now
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription successful',
                'data' => $subscriber
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to subscribe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified subscriber.
     */
    public function show(string $id)
    {
        try {
            $subscriber = Subscriber::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $subscriber
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscriber not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified subscriber.
     */
    public function update(Request $request, string $id)
    {
        try {
            $subscriber = Subscriber::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'email' => 'sometimes|required|email|unique:subscribers,email,' . $id,
                'name' => 'sometimes|nullable|string|max:255',
                'status' => 'sometimes|in:active,inactive,unsubscribed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $subscriber->update($request->only(['email', 'name', 'status']));

            return response()->json([
                'success' => true,
                'message' => 'Subscriber updated successfully',
                'data' => $subscriber
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subscriber',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified subscriber.
     */
    public function destroy(string $id)
    {
        try {
            $subscriber = Subscriber::findOrFail($id);
            $subscriber->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subscriber deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subscriber',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe a subscriber.
     */
    public function unsubscribe(string $id)
    {
        try {
            $subscriber = Subscriber::findOrFail($id);
            $subscriber->unsubscribe();

            return response()->json([
                'success' => true,
                'message' => 'Successfully unsubscribed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unsubscribe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subscriber statistics.
     */
    public function stats()
    {
        try {
            $totalSubscribers = Subscriber::count();
            $activeSubscribers = Subscriber::active()->count();
            $verifiedSubscribers = Subscriber::verified()->count();
            $unsubscribedCount = Subscriber::where('status', 'unsubscribed')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_subscribers' => $totalSubscribers,
                    'active_subscribers' => $activeSubscribers,
                    'verified_subscribers' => $verifiedSubscribers,
                    'unsubscribed_count' => $unsubscribedCount
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
