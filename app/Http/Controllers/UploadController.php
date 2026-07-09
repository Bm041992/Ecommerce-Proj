<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use App\Jobs\ProcessCsvJob;

class UploadController extends Controller
{
    public function index()
    {
        return view('upload');
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');

        $requiredHeaders = [
            'Handle',
            'Title',
            'Body HTML',
            'Vendor',
            'Product Type',
            'Tags',
            'Published',
            'Variant SKU',
            'Variant Price',
            'Variant Compare At Price',
            'Variant Requires Shipping',
            'Variant Taxable',
            'Variant Inventory Tracker',
            'Variant Inventory Qty',
            'Variant Inventory Policy',
            'Variant Fulfillment Service',
            'Variant Weight',
            'Variant Weight Unit',
            'Image Src',
            'Image Position',
            'Image Alt Text',
        ];

        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to open CSV file.'
            ]);
        }
        $headers = fgetcsv($handle);
        fclose($handle);

        if (!$headers) {
            return response()->json([
                'success' => false,
                'message' => 'CSV file is empty or has invalid headers.'
            ]);
        }

        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (!empty($missingHeaders)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid CSV format. Missing columns: ' . implode(', ', $missingHeaders)
            ]);
        }

        $path = $file->store('uploads');

        $upload = Upload::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'pending',
        ]);
        if ($upload) {
            ProcessCsvJob::dispatch($upload);
            return response()->json(['success' => true, 'message' => 'File uploaded successfully.']);
        }
        else {
            return response()->json(['success' => false, 'message' => 'File upload failed.']);
        }
    }
}
