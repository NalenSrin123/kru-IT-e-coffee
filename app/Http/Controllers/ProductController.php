<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

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
}