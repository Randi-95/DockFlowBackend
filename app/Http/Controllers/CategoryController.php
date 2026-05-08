<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getCategories(){
        $categories = Category::withCount('products')->get()->map(function($category){
            return [
                'id' => $category->id,
                'name' => $category->name,
                'total' => $category->products_count
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $categories
        ]);
    }
}
