<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getInformationProduct()
    {
        $totalStock = Product::all()->sum('stock_qty');
        $totalItem = Product::count();
        $lowStockProduct = Product::where('stock_qty', '<', 10)->count();

        return response()->json([
            'status' => true,
            'stock' => $totalStock,
            'totalItem' => $totalItem,
            'totalStokRendah' => $lowStockProduct
        ], 200);
    }

    public function getProducts(Request $request){
        $products = Product::query()
            ->when($request->category_id, function($query, $categoryId){
                return $query->where('category_id', $categoryId);
            })
            ->when($request->name, function($query, $name){
                return $query->where('name', 'LIKE', '%' . $name . '%');
            })
            ->get();

        return response()->json([
            'status' => true,
            'data' => $products
        ], 200);
    }

     public function getProductDetail($id){
        $product = Product::find($id);

        return response()->json([
            'status' => true,
            'data' => $product
        ], 200);
    }
}
