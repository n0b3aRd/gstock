<?php

namespace App\Http\Controllers;

use App\Http\Resources\GrnResource;
use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GrnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $gnrs = Grn::query()
            ->when(request('code'), function ($q, $code) {
            return $q->where('code', 'LIKE', '%'.$code.'%');
            })
            ->when(request('date'), function ($q, $date) {
                return $q->where('date', 'LIKE', '%'.$date.'%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return Inertia::render('Grn/list', [
            'grns' => GrnResource::collection($gnrs)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $grnItems = [[
           'id' => 0,
           'product_id' => '',
           'qty' => 0,
           'price' => 0,
        ]];
        $products = Inventory::select('name', 'id')->get();
        return Inertia::render('Grn/create', [
            'products' => $products,
            'grnItems' => $grnItems,
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
            'date' => 'required'
        ]);

        DB::beginTransaction();
        try {
            //save grn note
            $grn = new Grn();
            $grn->code = 'GRN';
            $grn->date = $request['date'];
            $grn->save();

            $grand_tot = 0;
            //add grn items and update qty in inventory
            foreach ($request['grnItems'] as $grnItem) {
                $item = new GrnItem();
                $item->grn_id = $grn->id;
                $item->product_id = $grnItem['product_id'];
                $item->qty = $grnItem['qty'];
                $item->price = $grnItem['price'];
                $item->save();

                $grand_tot += ($grnItem['qty'] * $grnItem['price']);

                $product = Inventory::find($grnItem['product_id']);
                if ($product) {
                    $product->qty = $product->qty + $grnItem['qty'];
                    $product->save();
                }
            }

            $grn->total = $grand_tot;
            $grn->code = 'GRN'.str_pad($grn->id, 5, '0', STR_PAD_LEFT);
            $grn->save();

            DB::commit();
            return redirect(route('grn.index'))->with('message', ['status' => 'success', 'message' => 'GRN added successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }

    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Grn  $grn
     * @return \Inertia\Response
     */
    public function edit(Grn $grn)
    {
        $grnItems = GrnItem::select('id', 'product_id', 'qty', 'price')
            ->where('grn_id', $grn->id)
            ->get();
        $products = Inventory::select('name', 'id')
            ->get();

        return Inertia::render('Grn/create', [
            'products' => $products,
            'grn' => new GrnResource($grn),
            'grnItems' => $grnItems,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Grn  $grn
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Grn $grn)
    {
        // do not allow to edit after 24h
        if (1440 < Carbon::now()->diffInMinutes($grn->created_at)) {
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Can not edit record after 24h']);
        }

        $request->validate([
            'date' => 'required'
        ]);

        DB::beginTransaction();
        try {
            //update grn note
            $grn->date = $request['date'];
            $grn->save();

            $old_grn_items = GrnItem::where('grn_id', $grn->id)->get();
            $common_in_both = [];
            $grand_tot = 0;

            foreach ($request['grnItems'] as $grnItem) {
                $old_item = $old_grn_items->where('product_id', $grnItem['product_id'])->first();
                if ($old_item) {
                    $diff = $grnItem['qty'] - $old_item->qty;
//                    if ($diff < 0) {
//                        //deduct count form inventory
//                        $product = Inventory::find($grnItem['product_id']);
//                        if ($product) {
//                            $product->qty = $product->qty - $diff;
//                            $product->save();
//                        }
//                    } else {
                        // update qty on inventory
                        $product = Inventory::find($grnItem['product_id']);
                        if ($product) {
                            $product->qty = $product->qty + $diff;
                            $product->save();
                        }
//                    }
                    //update old record in grn items
                    $old_item->qty = $grnItem['qty'];
                    $old_item->price = $grnItem['price'];
                    $old_item->save();

                    // add to common array
                    $common_in_both[] = $grnItem['product_id'];

                } else {
                    // add count to inventory
                    $product = Inventory::find($grnItem['product_id']);
                    if ($product) {
                        $product->qty = $product->qty + $grnItem['qty'];
                        $product->save();
                    }
                    // add to grn item
                    $item = new GrnItem();
                    $item->grn_id = $grn->id;
                    $item->product_id = $grnItem['product_id'];
                    $item->qty = $grnItem['qty'];
                    $item->price = $grnItem['price'];
                    $item->save();
                }
                //calculate new total
                $grand_tot += ($grnItem['qty'] * $grnItem['price']);
            }

            $removed_items = $old_grn_items->whereNotIn('product_id', $common_in_both);

            foreach ($removed_items as $item) {
                //deduct form inventory
                $product = Inventory::find($item->product_id);
                if ($product) {
                    $product->qty = $product->qty - $item->qty;
                    $product->save();
                }
                //remove item
                $item->delete();
            }

            //update grn totoal
            $grn->total = $grand_tot;
            $grn->save();

            DB::commit();
            return redirect(route('grn.index'))->with('message', ['status' => 'success', 'message' => 'GRN updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect(route('grn.index'))->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Grn  $grn
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Grn $grn)
    {
        DB::beginTransaction();
        try {
            GrnItem::where('grn_id', $grn->id)->delete();
            $grn->delete();

            DB::commit();
            return redirect(route('grn.index'))->with('message', ['status' => 'success', 'message' => 'GRN deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect(route('grn.index'))->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }
}
