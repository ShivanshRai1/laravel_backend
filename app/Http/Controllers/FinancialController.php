<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\FinancialData;

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
}