<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransferNoteResource;
use App\Models\Inventory;
use App\Models\ShopInventory;
use App\Models\TransferNote;
use App\Models\TransferNoteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransferNoteController extends Controller
{
    public function index()
    {
        $tnotes = TransferNote::latest()->paginate(10);
        return Inertia::render('TransferNote/list', [
            'tnotes' => TransferNoteResource::collection($tnotes)
        ]);
    }

    public function create()
    {
        $tnoteItems = [[
            'id' => 0,
            'product_id' => '',
            'qty' => 0,
            'price' => 0,
        ]];
        $products = Inventory::select('code', 'name', 'id', 'qty', 'price')->get();
        return Inertia::render('TransferNote/create', [
            'products' => $products,
            'tnoteItems' => $tnoteItems,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required'
        ]);

        DB::beginTransaction();
        try {

            //create new tnote
            $tnote = new TransferNote();
            $tnote->code = 'TN';
            $tnote->date = $request['date'];
            $tnote->save();

            $grand_tot = 0;
            foreach ($request['tnoteItems'] as $tnoteItem) {
                //create transfer note item
                $item = new TransferNoteItem();
                $item->transfer_note_id = $tnote->id;
                $item->product_id = $tnoteItem['product_id'];
                $item->qty = $tnoteItem['qty'];
                $item->price = $tnoteItem['price'];
                $item->save();

                $grand_tot += ($tnoteItem['qty'] * $tnoteItem['price']);

                //remove qty form inventory
                $product = Inventory::find($tnoteItem['product_id']);
                if ($product) {
                    $product->qty = $product->qty - $tnoteItem['qty'];
                    $product->save();
                }

                //add qty to store inventory
                $shop_product = ShopInventory::where('product_id', $tnoteItem['product_id'])->first();
                if (!$shop_product) {
                    $shop_product = new ShopInventory();
                    $shop_product->product_id = $tnoteItem['product_id'];
                    $shop_product->qty = $tnoteItem['qty'];
                    $shop_product->save();
                } else {
                    $shop_product->qty = $shop_product->qty + $tnoteItem['qty'];
                    $shop_product->save();
                }
            }

            $tnote->code = 'TN'.str_pad($tnote->id, 5, '0', STR_PAD_LEFT);
            $tnote->total = $grand_tot;
            $tnote->save();

            DB::commit();
            return redirect(route('tnote.index'))->with('message', ['status' => 'success', 'message' => 'Transfer Note added successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    public function edit($tnote)
    {
        $tnote = TransferNote::find($tnote);
        $tnoteItems = TransferNoteItem::select('id', 'product_id', 'qty', 'price')->where('transfer_note_id', $tnote->id)->get();
        $products = Inventory::select('code', 'name', 'id', 'qty', 'price')->get();
        return Inertia::render('TransferNote/create', [
            'tnote' => new TransferNoteResource($tnote),
            'products' => $products,
            'tnoteItems' => $tnoteItems,
        ]);
    }

    public function update(Request $request, $tnote)
    {
        return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Transfer Note can not update']);
    }

    public function destroy($tnote)
    {
        DB::beginTransaction();
        try {
            $tnote = TransferNote::find($tnote);
            TransferNoteItem::where('transfer_note_id', $tnote->id)->delete();
            $tnote->delete();

            DB::commit();
            return redirect(route('tnote.index'))->with('message', ['status' => 'success', 'message' => 'Transfer Note deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect(route('grn.index'))->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }
}
