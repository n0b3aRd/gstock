<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCategoryResource;
use App\Models\Inventory;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::query()
            ->when(request('name'), function ($q, $name) {
                return $q->where('name', 'LIKE', '%'.$name.'%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
        return Inertia::render('ProductCategory/list', [
            'categories' => ProductCategoryResource::collection($categories)
        ]);
    }

    public function create()
    {
        return Inertia::render('ProductCategory/create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:product_categories,name'
        ]);

        DB::beginTransaction();
        try {
            $category = new ProductCategory();
            $category->name = $request['name'];
            $category->save();

            DB::commit();
            return redirect(route('product-category.index'))->with('message', ['status' => 'success', 'message' => 'Product category added successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    public function edit($product_category)
    {
        $category = ProductCategory::find($product_category);
        return Inertia::render('ProductCategory/create', [
            'category' => $category
        ]);
    }

    public function update(Request $request, $product_category)
    {
        $request->validate([
            'name' => ['required', Rule::unique('product_categories', 'name')->ignore($product_category)]
        ]);

        DB::beginTransaction();
        try {
            $category = ProductCategory::find($product_category);
            $category->name = $request['name'];
            $category->save();

            DB::commit();
            return redirect(route('product-category.index'))->with('message', ['status' => 'success', 'message' => 'Product category updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    public function destroy($product_category)
    {
        $is_have_products = Inventory::where('category_id', $product_category)->first();
        if ($is_have_products) {
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Remove inventory items before delete category']);
        }

        try {
            $category = ProductCategory::find($product_category);
            $category->delete();

            return redirect(route('product-category.index'))->with('message', ['status' => 'success', 'message' => 'Product category deleted successfully']);
        } catch (\Exception $e) {
            addErrorToLog($e);
            return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Something went wrong']);
        }
    }
}
