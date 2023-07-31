<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShopInventoryResource;
use App\Models\Inventory;
use App\Models\ProductCategory;
use App\Models\ShopInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ShopInventoryController extends Controller
{
    public function index()
    {
        $inventory = Inventory::select('id')
            ->when(request('code'), function ($q, $code) {
                return $q->where('code', 'LIKE', '%'.$code.'%');
            })
            ->when(request('name'), function ($q, $name) {
                return $q->where('name', 'LIKE', '%'.$name.'%');
            })
            ->when(request('category_id'), function ($q, $category_id) {
                return $q->where('category_id', $category_id);
            })
            ->get()
            ->pluck('id')
            ->toArray();

        $shop_items = ShopInventory::with('product')
            ->when(count($inventory) > 0, function ($q, $in) use ($inventory){
                return $q->whereIn('product_id', $inventory);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
        $categories = ProductCategory::select('name', 'id')->get();

        return Inertia::render('Shop/list', [
            'shop_items' => ShopInventoryResource::collection($shop_items),
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $products = Inventory::select('code', 'name', 'id', 'qty', 'price')->get();
        return Inertia::render('Shop/create', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
           'product_id' => 'required|exists:inventories,id|unique:shop_inventories,product_id',
            'qty' => 'required|numeric'
        ]);

        DB::beginTransaction();
        try {

            $item = new ShopInventory();
            $item->product_id = $request['product_id'];
            $item->qty = $request['qty'];
            $item->save();

            DB::commit();
            return redirect(route('shop.index'))->with('message', ['status' => 'success', 'message' => 'Item added successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    public function edit($shop)
    {
        $item = ShopInventory::find($shop);
        $products = Inventory::select('code', 'name', 'id', 'qty', 'price')->get();
        return Inertia::render('Shop/create', [
            'item' => $item,
            'products' => $products,
        ]);
    }

    public function update(Request $request, $shop)
    {
        $request->validate([
            'product_id' => 'required|exists:inventories,id|unique:shop_inventories,product_id,'.$shop.',id',
            'qty' => 'required|numeric'
        ]);

        DB::beginTransaction();
        try {

            $item = ShopInventory::find($shop);
            $item->qty = $request['qty'];
            $item->save();

            DB::commit();
            return redirect(route('shop.index'))->with('message', ['status' => 'success', 'message' => 'Item updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    public function destroy($shop)
    {
        $item = ShopInventory::find($shop);
        if ($item) {
            $item->delete();
            return redirect(route('shop.index'))->with('message', ['status' => 'success', 'message' => 'Item deleted successfully']);
        }
        return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
    }
}
