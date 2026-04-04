<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Http\Request;

class ProductSizeController extends Controller
{
    /**
     * GET: បង្ហាញទំហំ និងតម្លៃទាំងអស់របស់ផលិតផលមួយ
     * GET /api/v1/products/{productId}/sizes
     */
    public function index($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return $this->errorResponse('Not found', 404);
        }

        $sizes = $product->sizes;

        return $this->successResponse($sizes, 'Sizes retrieved successfully');
    }


    /**
     * CREATE: បន្ថែមទំហំ និងតម្លៃថ្មី ចូលទៅក្នុងផលិតផលណាមួយ
     * POST /api/v1/products/{productId}/sizes
     */
    public function store(Request $request, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return $this->errorResponse('រកមិនឃើញផលិតផលនេះទេ', 404);
        }

        $request->validate([
            'name'      => 'required|string|max:50', // ឧទាហរណ៍៖ S, M, L ឬ តូច, កណ្តាល, ធំ
            'price'     => 'required|numeric|min:0', // តម្លៃមិនអាចអវិជ្ជមានទេ
            'is_active' => 'nullable|boolean',
        ]);

        // បង្កើតទំហំថ្មី ដោយចងភ្ជាប់ជាមួយ Product ID នោះដោយស្វ័យប្រវត្តិ
        $size = $product->sizes()->create($request->only(['name', 'price', 'is_active']));

        return $this->createdResponse($size, 'Size and price created successfully');
    }

    /**
     * UPDATE: កែប្រែទំហំ ឬតម្លៃ (ឧទាហរណ៍៖ ឡើងថ្លៃកាហ្វេ)
     * PUT /api/v1/sizes/{id}
     */
    public function update(Request $request, $id)
    {
        $size = ProductSize::find($id);

        if (!$size) {
            return $this->errorResponse('Size not found', 404);
        }

        $request->validate([
            'name'      => 'sometimes|required|string|max:50',
            'price'     => 'sometimes|required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $size->update($request->all());

        return $this->successResponse($size, 'Size and price updated successfully');
    }

    /**
     * DELETE: លុបទំហំណាមួយចោល
     * DELETE /api/v1/sizes/{id}
     */
    public function destroy($id)
    {
        $size = ProductSize::find($id);

        if (!$size) {
            return $this->errorResponse('Size not found', 404);
        }

        $size->delete();

        return $this->successResponse(null, 'Size deleted successfully');
    }
}
