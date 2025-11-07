<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\FinancialData;
use App\Models\UploadedFinancialData;

class FinancialController extends Controller
{
    public function getFinancialData(Request $request): JsonResponse
    {
        try {
            $query = FinancialData::with('company');
            
            // Add filtering if needed
            if ($request->has('company_id')) {
                $query->where('company_id', $request->company_id);
            }
            
            $data = $query->paginate($request->get('per_page', 10));
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get financial data for dashboard charts by company name
     */
    public function getDashboardFinancialData(Request $request): JsonResponse
    {
        try {
            $companyName = $request->get('company');
            
            if (!$companyName) {
                return response()->json([
                    'success' => false,
                    'error' => 'Company parameter is required'
                ], 400);
            }

            // Get financial data for the company from uploaded_financial_data
            $data = UploadedFinancialData::where('Company', 'like', '%' . $companyName . '%')
                ->get();

            // Transform the data into a format expected by the frontend
            $transformedData = [];
            
            foreach ($data as $row) {
                // Extract quarterly data and convert to yearly format
                $quarters = [
                    'CY_2022_Q4', 'CY_2023_Q1', 'CY_2023_Q2', 'CY_2023_Q3', 'CY_2023_Q4',
                    'CY_2024_Q1', 'CY_2024_Q2', 'CY_2024_Q3', 'CY_2024_Q4', 'CY_2025_Q1'
                ];
                
                foreach ($quarters as $quarter) {
                    if ($row->$quarter !== null && $row->$quarter !== '') {
                        $year = (int) substr($quarter, 3, 4);
                        $quarterNum = substr($quarter, -2);
                        
                        $transformedData[] = [
                            'company' => $row->Company,
                            'metric' => $row->Metrics,
                            'year' => $year,
                            'quarter' => $quarterNum,
                            'value' => is_numeric($row->$quarter) ? (float) $row->$quarter : $row->$quarter,
                            'currency' => $row->Currency,
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $transformedData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all companies with their financial data for dashboard
     */
    public function getAllDashboardData(): JsonResponse
    {
        try {
            $data = UploadedFinancialData::all();
            
            // Group by company and transform data
            $transformedData = [];
            
            foreach ($data as $row) {
                $quarters = [
                    'CY_2022_Q4', 'CY_2023_Q1', 'CY_2023_Q2', 'CY_2023_Q3', 'CY_2023_Q4',
                    'CY_2024_Q1', 'CY_2024_Q2', 'CY_2024_Q3', 'CY_2024_Q4', 'CY_2025_Q1'
                ];
                
                foreach ($quarters as $quarter) {
                    if ($row->$quarter !== null && $row->$quarter !== '') {
                        $year = (int) substr($quarter, 3, 4);
                        $quarterNum = substr($quarter, -2);
                        
                        $transformedData[] = [
                            'company' => $row->Company,
                            'metric' => $row->Metrics,
                            'year' => $year,
                            'quarter' => $quarterNum,
                            'value' => is_numeric($row->$quarter) ? (float) $row->$quarter : $row->$quarter,
                            'currency' => $row->Currency,
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $transformedData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}