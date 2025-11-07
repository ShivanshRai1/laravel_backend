<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Push Notification Service
 * 
 * Handles sending push notifications to registered devices.
 * 
 * SETUP INSTRUCTIONS:
 * 1. Get Firebase Server Key from Firebase Console > Project Settings > Cloud Messaging
 * 2. Add to .env: FIREBASE_SERVER_KEY=your_server_key_here
 * 3. Ensure ext-sodium is enabled in php.ini (required for full Firebase SDK)
 * 
 * Current Implementation: Uses Firebase Cloud Messaging HTTP v1 API
 * Future Enhancement: Use kreait/firebase-php when PHP compatibility is resolved
 */
class PushNotificationService
{
    protected $serverKey;
    protected $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = config('services.firebase.server_key');
    }

    /**
     * Send notification to a specific user
     *
     * @param int $userId User ID to send notification to
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Result of the notification send operation
     */
    public function sendToUser($userId, $title, $body, $data = [])
    {
        try {
            // Get all active device tokens for the user
            $devices = DeviceToken::where('user_id', $userId)
                ->where('enabled', true)
                ->get();

            if ($devices->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'No devices registered for user',
                    'sent' => 0
                ];
            }

            $results = [];
            $successCount = 0;
            $failureCount = 0;

            foreach ($devices as $device) {
                $result = $this->sendToDevice($device->device_token, $title, $body, $data);
                
                if ($result['success']) {
                    $successCount++;
                    // Update last used timestamp
                    $device->update(['last_used_at' => now()]);
                } else {
                    $failureCount++;
                    
                    // If token is invalid, disable it
                    if (isset($result['error']) && str_contains($result['error'], 'InvalidRegistration')) {
                        $device->update(['enabled' => false]);
                        Log::info("Disabled invalid device token for user {$userId}");
                    }
                }
                
                $results[] = $result;
            }

            return [
                'success' => true,
                'message' => "Sent to {$successCount} devices, failed for {$failureCount}",
                'sent' => $successCount,
                'failed' => $failureCount,
                'details' => $results
            ];

        } catch (\Exception $e) {
            Log::error('Error sending push notification to user: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to a specific device token
     *
     * @param string $deviceToken FCM device token
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Result of the notification send operation
     */
    public function sendToDevice($deviceToken, $title, $body, $data = [])
    {
        try {
            if (!$this->serverKey) {
                return [
                    'success' => false,
                    'error' => 'Firebase server key not configured. Add FIREBASE_SERVER_KEY to .env file.'
                ];
            }

            $payload = [
                'to' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => '/favicon.png',
                    'click_action' => url('/'),
                ],
                'data' => array_merge([
                    'timestamp' => now()->toIso8601String(),
                    'url' => url('/')
                ], $data)
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['success']) && $responseData['success'] > 0) {
                Log::info('Push notification sent successfully', [
                    'token' => substr($deviceToken, 0, 20) . '...',
                    'title' => $title
                ]);

                return [
                    'success' => true,
                    'message' => 'Notification sent successfully',
                    'response' => $responseData
                ];
            } else {
                $errorMessage = $responseData['results'][0]['error'] ?? 'Unknown error';
                
                Log::warning('Failed to send push notification', [
                    'token' => substr($deviceToken, 0, 20) . '...',
                    'error' => $errorMessage,
                    'response' => $responseData
                ]);

                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'response' => $responseData
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception sending push notification: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to multiple users
     *
     * @param array $userIds Array of user IDs
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array Result summary
     */
    public function sendToMultipleUsers($userIds, $title, $body, $data = [])
    {
        $totalSent = 0;
        $totalFailed = 0;

        foreach ($userIds as $userId) {
            $result = $this->sendToUser($userId, $title, $body, $data);
            if ($result['success']) {
                $totalSent += $result['sent'] ?? 0;
                $totalFailed += $result['failed'] ?? 0;
            }
        }

        return [
            'success' => true,
            'total_sent' => $totalSent,
            'total_failed' => $totalFailed
        ];
    }

    /**
     * Test notification - sends a test to the user
     *
     * @param int $userId User ID to send test notification to
     * @return array Result of the test
     */
    public function sendTestNotification($userId)
    {
        return $this->sendToUser(
            $userId,
            'Test Notification',
            'This is a test notification from Financial Dashboard',
            ['type' => 'test', 'test_time' => now()->toDateTimeString()]
        );
    }
}
