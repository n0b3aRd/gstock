<?php

namespace App\Http\Controllers;

use App\Models\Grn;
use App\Models\Inventory;
use App\Models\SalesNote;
use App\Models\TransferNote;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $product_count = Inventory::select('id')->count();
        $low_stock_items = Inventory::whereColumn('reorder_point', '>', 'qty')->count();
        $grn_count = Grn::select('id')->count();
        $last_gnr = Grn::latest()->first();
        $tnote_count = TransferNote::select('id')->count();
        $last_tnote = TransferNote::latest()->first();
        $snote_count = SalesNote::select('id')->count();
        $last_snote = SalesNote::latest()->first();
        return Inertia::render('Dashboard', [
            'data' => [
                'product_count' => $product_count,
                'low_stock_count' => $low_stock_items,
                'grn_count' => $grn_count,
                'tnote_count' => $tnote_count,
                'snote_count' => $snote_count,
                'last_grn' => ($last_gnr) ? 'Rs '.number_format($last_gnr->total, 2) : 'Rs 0.00',
                'last_tnote' => ($last_tnote) ? 'Rs '.number_format($last_tnote->total, 2) : 'Rs 0.00',
                'last_snote' => ($last_snote) ? 'Rs '.number_format($last_snote->total, 2) : 'Rs 0.00',
            ]
        ]);
    }
}
