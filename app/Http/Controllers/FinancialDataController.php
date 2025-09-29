<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\FinancialData;
// If you install Laravel Excel:
// use Maatwebsite\Excel\Facades\Excel;

class FinancialDataController extends Controller
{
    /**
     * Handle financial data file upload and import.
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $file = $request->file('file');
        $path = $file->store('uploads/financial_data');

        // TODO: Parse the file and insert data into financial_data table
        // Example: Use Laravel Excel for XLSX/CSV parsing
        // Excel::import(new FinancialDataImport, $file);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'path' => $path,
        ]);
    }
}
