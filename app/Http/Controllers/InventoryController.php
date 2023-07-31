<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $inventory = Inventory::with('category:id,name')
            ->when(request('code'), function ($q, $code) {
                return $q->where('code', 'LIKE', '%'.$code.'%');
            })
            ->when(request('name'), function ($q, $name) {
                return $q->where('name', 'LIKE', '%'.$name.'%');
            })
            ->when(request('category_id'), function ($q, $category_id) {
                return $q->where('category_id', $category_id);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
        $categories = ProductCategory::select('name', 'id')->get();
        return Inertia::render('Inventory/list', [
            'inventories' => InventoryResource::collection($inventory),
            'categories' => $categories,
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
            return redirect('inventory')->with('message', ['status' => 'success', 'message' => 'Record added successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    public function edit($inventory)
    {
        $inventory = Inventory::find($inventory);
        $categories = ProductCategory::select('name', 'id')->get();
        return Inertia::render('Inventory/create', [
            'categories' => $categories,
            'inventory' => $inventory
        ]);
    }

    public function update(StoreInventoryRequest $request, $inventory)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $item = Inventory::find($inventory);
            $item->code = $data['code'];
            $item->name = $data['name'];
            $item->category_id = $data['category_id'];
            $item->qty = $data['qty'] ?? 0;
            $item->reorder_point = $data['reorder_point'] ?? 0;
            $item->price = $data['price'] ?? 0;
            $item->save();

            DB::commit();
            return redirect('inventory')->with('message', ['status' => 'success', 'message' => 'Record updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    public function destroy ($inventory)
    {
        try {
            $inventory = Inventory::find($inventory);
            if ($inventory) {
                $inventory->delete();
                return redirect('inventory')->with('message', ['status' => 'success', 'message' => 'Record deleted successfully']);
            }
            return redirect('inventory')->with('message', ['status' => 'error', 'message' => 'Item not found']);
        } catch (\Exception $e) {
            addErrorToLog($e);
            return redirect('inventory')->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }
}
