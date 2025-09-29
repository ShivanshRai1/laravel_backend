<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Company;

class CompanyController extends Controller
{
    /**
     * List all companies
     */
    public function index(): JsonResponse
    {
        try {
            $companies = Company::all();
            return response()->json([
                'success' => true,
                'data' => $companies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new company
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'symbol' => 'required|string|max:50',
                'industry' => 'required|string|max:255',
            ]);
            $company = Company::create($request->only(['name', 'symbol', 'industry']));
            return response()->json([
                'success' => true,
                'data' => $company
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a company
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $company = Company::findOrFail($id);
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'symbol' => 'sometimes|required|string|max:50',
                'industry' => 'sometimes|required|string|max:255',
            ]);
            $company->update($request->only(['name', 'symbol', 'industry']));
            return response()->json([
                'success' => true,
                'data' => $company
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a company
     */
    public function destroy($id): JsonResponse
    {
        try {
            $company = Company::findOrFail($id);
            $company->delete();
            return response()->json([
                'success' => true,
                'message' => 'Company deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = Company::query();
            
            if ($request->has('q')) {
                $searchTerm = $request->get('q');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('symbol', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('industry', 'LIKE', "%{$searchTerm}%");
                });
            }
            
            $companies = $query->paginate($request->get('per_page', 10));
            
            return response()->json([
                'success' => true,
                'data' => $companies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}