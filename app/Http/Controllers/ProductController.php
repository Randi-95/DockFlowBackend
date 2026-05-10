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

    public function getInventoryData(Request $request){
        // Get statistics
        $totalStock = Product::all()->sum('stock_qty');
        $totalItem = Product::count();
        $lowStockProduct = Product::where('stock_qty', '<', 10)->count();
        $inOrderProduct = 0; // Bisa disesuaikan dengan logika booking

        // Get categories with product count
        $categories = Category::withCount('products')->get()->map(function($category){
            return [
                'id' => $category->id,
                'name' => $category->name,
                'icon_name' => $category->icon_name,
                'total' => $category->products_count
            ];
        });

        // Get products with filters
        $products = Product::with('category')
            ->when($request->category_id, function($query, $categoryId){
                return $query->where('category_id', $categoryId);
            })
            ->when($request->search, function($query, $search){
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                      ->orWhere('sku_code', 'LIKE', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($product){
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku_code' => $product->sku_code,
                    'category_name' => $product->category->name ?? '-',
                    'category_id' => $product->category_id,
                    'stock_qty' => $product->stock_qty,
                    'unit' => $product->unit,
                    'price' => $product->price,
                    'rack_location' => $product->rack_location,
                    'image_url' => $product->image_url,
                    'status' => $product->stock_qty < 10 ? 'Stok Rendah' : 'Tersedia',
                    'status_color' => $product->stock_qty < 10 ? 'orange' : 'green'
                ];
            });

        return response()->json([
            'status' => true,
            'data' => [
                'statistics' => [
                    'total_item' => $totalItem,
                    'total_stock' => $totalStock,
                    'low_stock' => $lowStockProduct,
                    'in_order' => $inOrderProduct
                ],
                'categories' => $categories,
                'products' => $products
            ]
        ], 200);
    }
}
