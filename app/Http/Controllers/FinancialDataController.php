<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\FinancialData;
use App\Models\Company;
use App\Models\UploadedFinancialData;

class FinancialDataController extends Controller
{
    /**
     * Handle financial data file upload and import.
     */
    public function upload(Request $request)
    {
        set_time_limit(180);
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'company_id' => 'required|exists:companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');
        $companyId = $request->company_id;
        $company = Company::find($companyId);
        
        $path = $file->store('uploads/financial_data');

        $inserted = 0;
        $errors = [];
        $originalFilename = $file->getClientOriginalName();
        $uploadedBy = $request->user() ? $request->user()->id : null;
        $uploadedAt = now();
        $batchId = Str::uuid()->toString();

        // Parse CSV file and map to table columns
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = null;
            $lineNumber = 0;
            
            while (($row = fgetcsv($handle, 10000, ',')) !== false) {
                $lineNumber++;
                
                if (!$header) {
                    $header = array_map('trim', $row);
                    continue;
                }
                
                if (count($header) !== count($row)) {
                    $errors[] = "Line $lineNumber: Column count mismatch";
                    continue;
                }
                
                $data = array_combine($header, array_map('trim', $row));
                
                try {
                    UploadedFinancialData::create([
                        'company_id' => $companyId,
                        'batch_id' => $batchId,
                        'Metrics' => $data['Metrics'] ?? null,
                        'Company' => $company->name,
                        'Currency' => $data['Currency'] ?? 'USD',
                        'CY_2025_Q1' => $data['CY 2025 Q1'] ?? null,
                        'CY_2024_Q4' => $data['CY 2024 Q4'] ?? null,
                        'CY_2024_Q3' => $data['CY 2024 Q3'] ?? null,
                        'CY_2024_Q2' => $data['CY 2024 Q2'] ?? null,
                        'CY_2024_Q1' => $data['CY 2024 Q1'] ?? null,
                        'CY_2023_Q4' => $data['CY 2023 Q4'] ?? null,
                        'CY_2023_Q3' => $data['CY 2023 Q3'] ?? null,
                        'CY_2023_Q2' => $data['CY 2023 Q2'] ?? null,
                        'CY_2023_Q1' => $data['CY 2023 Q1'] ?? null,
                        'CY_2022_Q4' => $data['CY 2022 Q4'] ?? null,
                        'CY_2022_Q3' => $data['CY 2022 Q3'] ?? null,
                        'CY_2022_Q2' => $data['CY 2022 Q2'] ?? null,
                        'original_filename' => $originalFilename,
                        'uploaded_by' => $uploadedBy,
                        'uploaded_at' => $uploadedAt,
                        'is_manual' => false,
                    ]);
                    $inserted++;
                } catch (\Exception $e) {
                    $errors[] = "Line $lineNumber: " . $e->getMessage();
                }
            }
            fclose($handle);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to open uploaded file for parsing.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'File uploaded and parsed.',
            'company' => $company->name,
            'batch_id' => $batchId,
            'path' => $path,
            'inserted' => $inserted,
            'errors' => $errors,
        ]);
    }

    /**
     * Get upload history
     */
    public function getUploadHistory(Request $request)
    {
        try {
            $query = UploadedFinancialData::with(['company', 'uploader'])
                ->select('batch_id', 'company_id', 'original_filename', 'uploaded_by', 'uploaded_at')
                ->selectRaw('COUNT(*) as record_count')
                ->groupBy('batch_id', 'company_id', 'original_filename', 'uploaded_by', 'uploaded_at')
                ->orderBy('uploaded_at', 'desc');

            if ($request->has('company_id')) {
                $query->where('company_id', $request->company_id);
            }

            $uploads = $query->limit(20)->get();

            return response()->json([
                'success' => true,
                'data' => $uploads
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an upload batch
     */
    public function deleteUploadBatch($batchId)
    {
        try {
            $deleted = UploadedFinancialData::where('batch_id', $batchId)->delete();

            return response()->json([
                'success' => true,
                'message' => "$deleted records deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
