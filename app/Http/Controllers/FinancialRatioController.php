<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\FinancialRatio;
use App\Models\FinancialMetric;
use App\Models\Company;

class FinancialRatioController extends Controller
{
    /**
     * Get all ratios for a specific company
     */
    public function getByCompany($companyId): JsonResponse
    {
        try {
            $company = Company::findOrFail($companyId);
            
            $ratios = FinancialRatio::with(['metric'])
                ->where('company_id', $companyId)
                ->get()
                ->groupBy('metric.display_name');
            
            return response()->json([
                'success' => true,
                'company' => $company,
                'data' => $ratios
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a single ratio value
     */
    public function updateMetric(Request $request, $companyId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'metric_id' => 'required|exists:financial_metrics,id',
                'quarter' => 'required|string',
                'value' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $ratio = FinancialRatio::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'metric_id' => $request->metric_id,
                    'quarter' => $request->quarter,
                ],
                [
                    'value' => $request->value,
                    'is_manual' => true,
                    'updated_by' => $request->user()->id,
                    'created_by' => $request->user()->id,
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $ratio->load('metric')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update ratios
     */
    public function bulkUpdate(Request $request, $companyId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'metrics' => 'required|array',
                'metrics.*.metric_id' => 'required|exists:financial_metrics,id',
                'metrics.*.quarter' => 'required|string',
                'metrics.*.value' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $updated = 0;
            foreach ($request->metrics as $metricData) {
                FinancialRatio::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'metric_id' => $metricData['metric_id'],
                        'quarter' => $metricData['quarter'],
                    ],
                    [
                        'value' => $metricData['value'] ?? null,
                        'is_manual' => true,
                        'updated_by' => $request->user()->id,
                        'created_by' => $request->user()->id,
                    ]
                );
                $updated++;
            }

            return response()->json([
                'success' => true,
                'message' => "$updated ratios updated successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available financial metrics
     */
    public function getMetrics(): JsonResponse
    {
        try {
            $metrics = FinancialMetric::active()
                ->orderBy('category')
                ->orderBy('sort_order')
                ->orderBy('display_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new custom metric
     */
    public function createMetric(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:financial_metrics,name',
                'display_name' => 'required|string',
                'type' => 'required|in:currency,percentage,ratio,count,text',
                'unit' => 'nullable|string',
                'category' => 'nullable|string',
                'formula' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $metric = FinancialMetric::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'type' => $request->type,
                'unit' => $request->unit,
                'category' => $request->category ?? 'Custom',
                'formula' => $request->formula,
                'is_custom' => true,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'data' => $metric
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a custom metric
     */
    public function deleteMetric($id): JsonResponse
    {
        try {
            $metric = FinancialMetric::findOrFail($id);
            
            if (!$metric->is_custom) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete standard metrics'
                ], 403);
            }

            $metric->delete();

            return response()->json([
                'success' => true,
                'message' => 'Metric deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
