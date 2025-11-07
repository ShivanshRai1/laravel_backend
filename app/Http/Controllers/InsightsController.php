<?php

namespace App\Http\Controllers;

use App\Services\InsightsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InsightsController extends Controller
{
    protected $insightsService;

    public function __construct(InsightsService $insightsService)
    {
        $this->insightsService = $insightsService;
    }

    /**
     * Get AI-generated insights for a company
     */
    public function getCompanyInsights(Request $request, string $ticker): JsonResponse
    {
        try {
            $insights = $this->insightsService->generateInsights($ticker);

            return response()->json([
                'success' => true,
                'data' => $insights,
                'ticker' => $ticker,
                'generated_at' => now()->toIso8601String()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate insights',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
