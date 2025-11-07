<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SavedComparison;
use Illuminate\Support\Facades\Validator;

class SavedComparisonController extends Controller
{
    /**
     * Get user's saved comparisons
     */
    public function index()
    {
        try {
            $savedComparisons = Auth::user()->savedComparisons()->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $savedComparisons,
                'message' => 'Saved comparisons retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve saved comparisons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new saved comparison
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'companies' => 'required|array|min:2',
            'companies.*' => 'required|string',
            'comparison_data' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $savedComparison = Auth::user()->savedComparisons()->create([
                'name' => $request->name,
                'companies' => $request->companies,
                'comparison_data' => $request->comparison_data,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'data' => $savedComparison,
                'message' => 'Comparison saved successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save comparison',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific saved comparison
     */
    public function show($id)
    {
        try {
            $savedComparison = Auth::user()->savedComparisons()->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $savedComparison,
                'message' => 'Saved comparison retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Saved comparison not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update a saved comparison
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'companies' => 'sometimes|required|array|min:2',
            'companies.*' => 'required|string',
            'comparison_data' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $savedComparison = Auth::user()->savedComparisons()->findOrFail($id);
            $savedComparison->update($request->only(['name', 'companies', 'comparison_data', 'description']));

            return response()->json([
                'success' => true,
                'data' => $savedComparison,
                'message' => 'Comparison updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update comparison',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a saved comparison
     */
    public function destroy($id)
    {
        try {
            $savedComparison = Auth::user()->savedComparisons()->findOrFail($id);
            $savedComparison->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comparison deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete comparison',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}