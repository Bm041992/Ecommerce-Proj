<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $products = Product::orderByDesc('id');

            return DataTables::of($products)

                ->addColumn('status', function ($row) {
                    return match ($row->status) {
                        1 => '<span class="badge bg-secondary">Pending</span>',
                        2 => '<span class="badge bg-warning">Processing</span>',
                        3 => '<span class="badge bg-success">Successful</span>',
                        4 => '<span class="badge bg-danger">Failed</span>',
                        default => '-',
                    };
                })
                ->filterColumn('status', function ($query, $keyword) {

                    $status = match (strtolower($keyword)) {
                        'pending' => 1,
                        'processing' => 2,
                        'successful', 'success' => 3,
                        'failed' => 4,
                        default => null,
                    };

                    if (!is_null($status)) {
                        $query->where('status', $status);
                    }
                })

                ->editColumn('error_message', function ($row) {
                    return $row->error_message ?: '-';
                })

                ->rawColumns(['status'])

                ->make(true);
        }

        return view('dashboard');
    }

}
