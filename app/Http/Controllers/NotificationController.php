<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EmailAlertPreference;
use App\Models\WeeklyDigestSubscription;
use App\Models\NewsletterSubscription;
use App\Models\DeviceToken;
use App\Models\AlertHistory;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $notifications = $request->user()
                ->notifications()
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $notifications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $count = $request->user()
                ->unreadNotifications()
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        try {
            $notification = $request->user()
                ->notifications()
                ->findOrFail($id);

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $request->user()
                ->unreadNotifications()
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a notification
     */
    public function delete(Request $request, $id): JsonResponse
    {
        try {
            $notification = $request->user()
                ->notifications()
                ->findOrFail($id);

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all email alert preferences for the authenticated user
     */
    public function getEmailAlerts(): JsonResponse
    {
        try {
            $user = auth()->user();
            $alerts = EmailAlertPreference::where('user_id', $user->id)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new email alert preference
     */
    public function createEmailAlert(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'nullable|string',
                'alert_type' => 'required|in:new_data,ratio_change,all',
                'threshold' => 'nullable|numeric|min:0|max:100',
                'watched_ratios' => 'nullable|array',
                'enabled' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 422);
            }

            $user = auth()->user();
            
            $alert = EmailAlertPreference::create([
                'user_id' => $user->id,
                'company_id' => $request->company_id,
                'alert_type' => $request->alert_type,
                'threshold' => $request->threshold ?? 5.0,
                'watched_ratios' => $request->watched_ratios,
                'enabled' => $request->enabled ?? true,
            ]);

            return response()->json([
                'success' => true,
                'data' => $alert,
                'message' => 'Email alert created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an email alert preference
     */
    public function updateEmailAlert(Request $request, $id): JsonResponse
    {
        try {
            $user = auth()->user();
            $alert = EmailAlertPreference::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$alert) {
                return response()->json([
                    'success' => false,
                    'error' => 'Alert not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'company_id' => 'nullable|string',
                'alert_type' => 'sometimes|in:new_data,ratio_change,all',
                'threshold' => 'nullable|numeric|min:0|max:100',
                'watched_ratios' => 'nullable|array',
                'enabled' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 422);
            }

            $alert->update($request->only([
                'company_id',
                'alert_type',
                'threshold',
                'watched_ratios',
                'enabled'
            ]));

            return response()->json([
                'success' => true,
                'data' => $alert,
                'message' => 'Email alert updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an email alert preference
     */
    public function deleteEmailAlert($id): JsonResponse
    {
        try {
            $user = auth()->user();
            $alert = EmailAlertPreference::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$alert) {
                return response()->json([
                    'success' => false,
                    'error' => 'Alert not found'
                ], 404);
            }

            $alert->delete();

            return response()->json([
                'success' => true,
                'message' => 'Email alert deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weekly digest subscription
     */
    public function getWeeklyDigest(): JsonResponse
    {
        try {
            $user = auth()->user();
            $subscription = WeeklyDigestSubscription::where('user_id', $user->id)->first();

            return response()->json([
                'success' => true,
                'data' => $subscription
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update weekly digest subscription
     */
    public function updateWeeklyDigest(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'day_of_week' => 'sometimes|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'preferred_time' => 'sometimes|date_format:H:i:s',
                'enabled' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 422);
            }

            $user = auth()->user();
            
            $subscription = WeeklyDigestSubscription::updateOrCreate(
                ['user_id' => $user->id],
                $request->only(['day_of_week', 'preferred_time', 'enabled'])
            );

            return response()->json([
                'success' => true,
                'data' => $subscription,
                'message' => 'Weekly digest preferences updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register a device token for push notifications
     */
    public function registerDevice(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'device_token' => 'required|string|max:500',
                'device_type' => 'required|in:web,android,ios',
                'device_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 422);
            }

            $user = auth()->user();
            
            $device = DeviceToken::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'device_token' => $request->device_token
                ],
                [
                    'device_type' => $request->device_type,
                    'device_name' => $request->device_name,
                    'enabled' => true,
                    'last_used_at' => now()
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $device,
                'message' => 'Device registered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unregister a device token
     */
    public function unregisterDevice(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'device_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 422);
            }

            $user = auth()->user();
            
            $deleted = DeviceToken::where('user_id', $user->id)
                ->where('device_token', $request->device_token)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Device unregistered successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Device not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification history
     */
    public function getAlertHistory(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $limit = $request->get('limit', 50);
            
            $history = AlertHistory::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

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
     * Get newsletter subscription preferences for the authenticated user
     */
    public function getNewsletterSubscription(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            // Get or create default newsletter subscription settings
            $subscription = NewsletterSubscription::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'subscribed' => false,
                    'frequency' => 'weekly',
                    'categories' => json_encode(['market-updates', 'company-news']),
                    'format' => 'html',
                    'enabled' => true
                ]
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'subscribed' => $subscription->subscribed,
                    'frequency' => $subscription->frequency,
                    'categories' => json_decode($subscription->categories, true),
                    'format' => $subscription->format,
                    'enabled' => $subscription->enabled
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update newsletter subscription preferences for the authenticated user
     */
    public function updateNewsletterSubscription(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subscribed' => 'required|boolean',
                'frequency' => 'nullable|in:daily,weekly,monthly',
                'categories' => 'nullable|array',
                'categories.*' => 'string',
                'format' => 'nullable|in:html,text'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 422);
            }

            $user = auth()->user();

            // Get or create newsletter subscription
            $subscription = NewsletterSubscription::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'subscribed' => false,
                    'frequency' => 'weekly',
                    'categories' => json_encode(['market-updates', 'company-news']),
                    'format' => 'html',
                    'enabled' => true
                ]
            );

            // Update the subscription with provided data
            $subscription->subscribed = $request->subscribed;
            
            if ($request->has('frequency')) {
                $subscription->frequency = $request->frequency;
            }
            
            if ($request->has('categories')) {
                $subscription->categories = json_encode($request->categories);
            }
            
            if ($request->has('format')) {
                $subscription->format = $request->format;
            }

            $subscription->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'subscribed' => $subscription->subscribed,
                    'frequency' => $subscription->frequency,
                    'categories' => json_decode($subscription->categories, true),
                    'format' => $subscription->format,
                    'enabled' => $subscription->enabled
                ],
                'message' => 'Newsletter subscription updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
