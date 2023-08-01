<?php

namespace App\Http\Controllers;

use App\Http\Resources\InventoryResource;
use App\Http\Resources\SupplierResource;
use App\Models\Inventory;
use App\Models\ShopInventory;
use App\Models\Supplier;
use App\Models\SupplierItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $supplier_ids = [];
        if (request('product_id')) {
            $supplier_ids = SupplierItem::select('supplier_id')
                ->where('product_id', $request->query('product_id'))
                ->get()
                ->pluck('supplier_id')
                ->toArray();
        }
        $suppliers = Supplier::with('items.product')
            ->when(count($supplier_ids) > 0, function ($q, $in) use($supplier_ids) {
                return $q->whereIn('id', $supplier_ids);
            })
            ->when(request('name'), function ($q, $name) {
                return $q->where('name', 'LIKE', '%'.$name.'%');
            })
            ->when(request('phone'), function ($q, $phone) {
                return $q->where('phone', 'LIKE', '%'.$phone.'%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
        $inventory = Inventory::select('id', 'name')->get();
        return Inertia::render('Supplier/list', [
            'suppliers' => SupplierResource::collection($suppliers),
            'inventory' => $inventory
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $inventory = Inventory::select('id', 'name')->get();
        return Inertia::render('Supplier/create', [
            'inventory' => $inventory
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|max:15|min:10',
        ]);

        DB::beginTransaction();
        try {
            //save supplier
            $supplier = new Supplier();
            $supplier->name = $request->name;
            $supplier->phone = $request->phone;
            $supplier->address = $request->address ?? null;
            $supplier->save();

            if (isset($request->items) && count($request->items) > 0) {
                //add supplier items
                foreach ($request->items as $item) {
                    $supplier_item = new SupplierItem();
                    $supplier_item->supplier_id = $supplier->id;
                    $supplier_item->product_id = $item['id'];
                    $supplier_item->save();
                }
            }
            DB::commit();
            return redirect(route('suppliers.index'))->with('message', ['status' => 'success', 'message' => 'Supplier added successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show(Supplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Inertia\Response
     */
    public function edit(Supplier $supplier)
    {
        $inventory = Inventory::select('id', 'name')->get();
        return Inertia::render('Supplier/create', [
            'inventory' => $inventory,
            'supplier' => new SupplierResource($supplier),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|max:15|min:10',
        ]);

        DB::beginTransaction();
        try {
            //update supplier
            $supplier->name = $request->name;
            $supplier->phone = $request->phone;
            $supplier->address = $request->address ?? null;
            $supplier->save();

            $old_sup_items = SupplierItem::where('supplier_id', $supplier->id)->get();
            $in_common = [];

            if (isset($request->items) && count($request->items) > 0) {
                //add supplier items
                foreach ($request->items as $item) {
                    $old_sup_item = $old_sup_items->where('product_id', $item['id'])->first();
                    if ($old_sup_item) {
                        $in_common[] = $item['id'];
                        continue;
                    } else {
                        $supplier_item = new SupplierItem();
                        $supplier_item->supplier_id = $supplier->id;
                        $supplier_item->product_id = $item['id'];
                        $supplier_item->save();
                    }
                }
                $remove_items = $old_sup_items->whereNotIn('product_id', $in_common);
                foreach ($remove_items as $item) {
                    $item->delete();
                }
            } else {
                foreach ($old_sup_items as $item) {
                    $item->delete();
                }
            }
            DB::commit();
            return redirect(route('suppliers.index'))->with('message', ['status' => 'success', 'message' => 'Supplier updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Supplier $supplier)
    {
        try {
            //remove supplier items
            $sup_items = SupplierItem::where('supplier_id', $supplier->id)->get();
            foreach ($sup_items as $item) {
                $item->delete();
            }
            //remove supplier
            $supplier->delete();

            DB::commit();
            return redirect(route('suppliers.index'))->with('message', ['status' => 'success', 'message' => 'Supplier deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }
}
