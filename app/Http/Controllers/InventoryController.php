<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/list', [
            'inventories' => InventoryResource::collection(Inventory::paginate(3))
        ]);
    }

    public function create()
    {
        $categories = ProductCategory::select('name', 'id')->get();
        return Inertia::render('Inventory/create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreInventoryRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $item = new Inventory();
            $item->code = $data['code'];
            $item->name = $data['name'];
            $item->category_id = $data['category_id'];
            $item->qty = $data['qty'] ?? 0;
            $item->reorder_point = $data['reorder_point'] ?? 0;
            $item->price = $data['price'] ?? 0;
            $item->save();

            DB::commit();
            return redirect('inventory');

        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            dd($e->getMessage());
        }
    }
}
