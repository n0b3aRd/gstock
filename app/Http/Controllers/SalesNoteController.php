<?php

namespace App\Http\Controllers;

use App\Http\Resources\SalesResource;
use App\Http\Resources\ShopInventoryResource;
use App\Models\Inventory;
use App\Models\SalesNote;
use App\Models\SalesNoteItem;
use App\Models\ShopInventory;
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
     * @param  \App\Models\SalesNote  $salesNote
     * @return \Illuminate\Http\Response
     */
    public function edit(SalesNote $salesNote)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SalesNote  $salesNote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SalesNote $salesNote)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesNote  $salesNote
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesNote $salesNote)
    {
        //
    }
}
