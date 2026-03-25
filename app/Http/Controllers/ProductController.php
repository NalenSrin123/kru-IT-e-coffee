<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;


class ProductController extends Controller
{
    // READ ALL
    public function index()
    {
        $products = Product::with('category','sizes')->get();
        return response()->json($products);
    }

    // READ ONE
    public function show($id)
    {
        $product = Product::with('category','sizes')->find($id);

        if(!$product){
            return response()->json(['message'=>'Product not found'],404);
        }

        return response()->json($product);
    }

    // CREATE
    public function store(Request $request)
    {
        $product = Product::create([
            'category_id'=>$request->category_id,
            'name'=>$request->name,
            'slug'=>$request->slug,
            'sku'=>$request->sku,
            'description'=>$request->description,
            'image_url'=>$request->image_url,
            'is_available'=>$request->is_available,
            'is_active'=>$request->is_active,
        ]);

        return response()->json([
            'message'=>'Product created',
            'data'=>$product
        ]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if(!$product){
            return response()->json(['message'=>'Product not found'],404);
        }

        $product->update($request->all());

        return response()->json([
            'message'=>'Product updated',
            'data'=>$product
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $product = Product::find($id);

        if(!$product){
            return response()->json(['message'=>'Product not found'],404);
        }

        $product->delete();

        return response()->json([
            'message'=>'Product deleted'
        ]);
    }

    public function byCategory(Category $category){
        $products = Product::with('category')
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->latest()
            ->paginate(8);
        return ProductResource::collection($products)->additional([
            'success' => true,
            'message' => 'Products by category retrieved successfully',
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
        ]);
    }
    public function product(Request $request){
        $query = Product::with('category')->where('is_active', true);

        if($request->filled('category_id')){
            $query->where('category_id', $request->category_id);
        }

        if($request->filled('category_slug')){
            $query->whereHas('category', function($q) use ($request){
                $q->where('slug', $request->category_slug);
            });
        }

        if($request->filled('search')){
            $query->where('name', 'like', '%'.$request->search.'%');
        }   

        $products = $query->latest()->paginate(8);

        return ProductResource::collection($products)->additional([
            'success' => true,
            'message' => 'Products retrieved successfully',
        ]);
    }
}