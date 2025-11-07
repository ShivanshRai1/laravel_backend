<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;

class CompanyController extends Controller
{
    /**
     * List all companies without trying to fetch financial data
     */
    public function index(): JsonResponse
    {
        try {
            // Just get companies without trying to join financial data
            $companies = Company::all();
            
            // Return companies as is without trying to get financial data
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
            
            // Handle search term if provided
            if ($request->has('q') && $request->get('q') !== null && $request->get('q') !== '') {
                $searchTerm = $request->get('q');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('symbol', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('sector', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('industry', 'LIKE', "%{$searchTerm}%");
                });
            }
            
            // If no search term, return all companies (no pagination for dashboard search)
            $perPage = $request->get('per_page', null);
            $companies = $perPage ? $query->paginate($perPage) : $query->get();
            
            // Map companies to ensure consistent format without financial data
            $formattedCompanies = $companies->map(function($company) {
                // Just use basic company data without financial data
                $result = $company->toArray();
                
                // Add mock price and change data for frontend display
                $result['latest_price'] = mt_rand(5000, 50000) / 100; // Random price between $50-$500
                $result['price_change'] = mt_rand(-500, 500) / 100; // Random change between -5% and +5%
                
                // Make sure we have ticker for frontend
                $result['ticker'] = $company->symbol;
                
                // Return the formatted company data
                return $result;
            });
            
            return response()->json([
                'success' => true,
                'data' => $formattedCompanies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Fetch a single company by symbol or name (case-insensitive)
     * With mock financial data
     */
    public function showByIdentifier($identifier): JsonResponse
    {
        // Try to find company by symbol or name (case-insensitive)
        $company = Company::whereRaw('LOWER(symbol) = ?', [strtolower($identifier)])
            ->orWhereRaw('LOWER(name) = ?', [strtolower($identifier)])
            ->first();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], 404);
        }

        // Get basic company data
        $result = $company->toArray();
        
        // Add mock price and financial data for testing
        $result['latest_price'] = mt_rand(5000, 50000) / 100; // Random price between $50-$500
        $result['price_change'] = mt_rand(-500, 500) / 100; // Random change between -5% and +5%
        $result['volume'] = mt_rand(100000, 10000000); // Random volume
        $result['date'] = date('Y-m-d');
        
        // Make sure we use symbol as an identifier
        $result['ticker'] = $company->symbol;
        
        // Add mock historical data
        $historicalData = [];
        $basePrice = $result['latest_price'];
        
        // Generate 30 days of mock data
        for ($i = 30; $i > 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayPrice = $basePrice * (1 + (mt_rand(-300, 300) / 10000)); // Small daily fluctuation
            
            $historicalData[] = [
                'date' => $date,
                'open_price' => round($dayPrice * 0.99, 2),
                'high_price' => round($dayPrice * 1.02, 2),
                'low_price' => round($dayPrice * 0.98, 2),
                'close_price' => round($dayPrice, 2),
                'volume' => mt_rand(100000, 10000000)
            ];
        }

        // Add the historical data to the result
        $result['historical_data'] = $historicalData;
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}