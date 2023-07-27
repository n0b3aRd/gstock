<?php

namespace App\Http\Controllers;

use App\Http\Resources\SalesResource;
use App\Http\Resources\ShopInventoryResource;
use App\Models\Inventory;
use App\Models\SalesNote;
use App\Models\SalesNoteItem;
use App\Models\ShopInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SalesNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $sales = SalesNote::latest()->paginate(10);
        return Inertia::render('SalesNote/list', [
            'sales' => SalesResource::collection($sales)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $salesItems = [[
            'id' => 0,
            'product_id' => '',
            'qty' => 0,
            'price' => 0,
        ]];
        $products = ShopInventory::with('product')->get();
        return Inertia::render('SalesNote/create', [
            'products' => ShopInventoryResource::collection($products),
            'salesItems' => $salesItems,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required'
        ]);

        DB::beginTransaction();
        try {

            //create sale note
            $snote = new SalesNote();
            $snote->code = 'SN';
            $snote->date = $request['date'];
            $snote->save();

            $grand_tot = 0;
            foreach ($request['salesItems'] as $salesItem) {
                //get item from shop
                $shop_item = ShopInventory::where('product_id', $salesItem['product_id'])->first();
                if (!$shop_item) {
                   DB::rollBack();
                    return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Product not found at shop']);
                }
                //check shop item qty
                if ($shop_item->qty < $salesItem['qty']) {
                    DB::rollBack();
                    return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Insufficient product quantity in shop']);
                }
                //create sale note item
                $snote_item = new SalesNoteItem();
                $snote_item->sales_note_id = $snote->id;
                $snote_item->product_id = $salesItem['product_id'];
                $snote_item->qty = $salesItem['qty'];
                $snote_item->price = $salesItem['price'];
                $snote_item->save();

                $grand_tot += ($salesItem['qty'] * $salesItem['price']);

                //remove qty from shop item
                $shop_item->qty = $shop_item->qty - $salesItem['qty'];
                $shop_item->save();
            }

            $snote->code = 'SN'.str_pad($snote->id, 5, '0', STR_PAD_LEFT);
            $snote->total = $grand_tot;
            $snote->save();

            DB::commit();
            return redirect(route('sales.index'))->with('message', ['status' => 'success', 'message' => 'Sales Note added successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalesNote  $salesNote
     * @return \Illuminate\Http\Response
     */
    public function show(SalesNote $salesNote)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Inertia\Response
     */
    public function edit($sale)
    {
        $sale = SalesNote::find($sale);
        $salesItems = SalesNoteItem::select('id', 'product_id', 'qty', 'price')->where('sales_note_id', $sale->id)->get();
        $products = ShopInventory::with('product')->get();
        return Inertia::render('SalesNote/edit', [
            'products' => ShopInventoryResource::collection($products),
            'salesItems' => $salesItems,
            'sale' => $sale,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $sale)
    {
        $snote = SalesNote::find($sale);

        // do not allow to edit after 24h
        if (1440 < Carbon::now()->diffInMinutes($snote->created_at)) {
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Can not edit record after 24h']);
        }

        $request->validate([
            'date' => 'required'
        ]);

        DB::beginTransaction();
        try {
            //update sale note
            $snote->date = $request['date'];
            $snote->save();

            $old_sale_note_items = SalesNoteItem::where('sales_note_id', $snote->id)->get();
            $common_in_both = [];
            $grand_tot = 0;

            foreach ($request['salesItems'] as $salesItem) {
                $old_item = $old_sale_note_items->where('product_id', $salesItem['product_id'])->first();
                if ($old_item) {
                    $diff = $old_item->qty - $salesItem['qty'];

                    //update qty on store inventory
                    $product = ShopInventory::where('product_id', $salesItem['product_id'])->first();
                    if ($product) {
                        $product->qty = $product->qty + $diff;
                        $product->save();
                    }

                    //update old record in sale items
                    $old_item->qty = $salesItem['qty'];
                    $old_item->price = $salesItem['price'];
                    $old_item->save();

                    // add to common array
                    $common_in_both[] = $salesItem['product_id'];
                } else {
                    //get item from shop
                    $shop_item = ShopInventory::where('product_id', $salesItem['product_id'])->first();
                    if (!$shop_item) {
                        DB::rollBack();
                        return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Product not found at shop']);
                    }
                    //check shop item qty
                    if ($shop_item->qty < $salesItem['qty']) {
                        DB::rollBack();
                        return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Insufficient product quantity in shop']);
                    }
                    //create sale note item
                    $snote_item = new SalesNoteItem();
                    $snote_item->sales_note_id = $snote->id;
                    $snote_item->product_id = $salesItem['product_id'];
                    $snote_item->qty = $salesItem['qty'];
                    $snote_item->price = $salesItem['price'];
                    $snote_item->save();

                    //remove qty from shop item
                    $shop_item->qty = $shop_item->qty - $salesItem['qty'];
                    $shop_item->save();
                }
                $grand_tot += ($salesItem['qty'] * $salesItem['price']);
            }

            $removed_items = $old_sale_note_items->whereNotIn('product_id', $common_in_both);

            foreach ($removed_items as $item) {
                //add to shop inventory
                $product = ShopInventory::where('product_id', $item->product_id)->first();
                if ($product) {
                    $product->qty = $product->qty + $item->qty;
                    $product->save();
                }
                //remove item
                $item->delete();
            }

            $snote->total = $grand_tot;
            $snote->save();

            DB::commit();
            return redirect(route('sales.index'))->with('message', ['status' => 'success', 'message' => 'Sales Note updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($sale)
    {
        DB::beginTransaction();
        try {
            $sale = SalesNote::find($sale);
            SalesNoteItem::where('sales_note_id', $sale->id)->delete();
            $sale->delete();

            DB::commit();
            return redirect(route('sales.index'))->with('message', ['status' => 'success', 'message' => 'Sale Note deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect(route('sales.index'))->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }
}
