<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * READ ALL: ទាញយកបញ្ជីទំនិញទាំងអស់ (មាន Pagination និង Search)
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'sizes']);

        // មុខងារ Search ឈ្មោះ ឬ SKU
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('sku', 'like', '%' . $request->search . '%');
        }

        // Filter តាម Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('id', 'desc')->paginate($request->get('per_page', 8));

        return $this->successResponse($products, 'Get all products successfully');
    }

    /**
     * READ ONE: មើលលម្អិតទំនិញណាមួយ
     */
    public function show($id)
    {
        $product = Product::with(['category', 'sizes'])->find($id);

        if (!$product) {
            return $this->errorResponse('No product found', 404);
        }

        return $this->successResponse($product, 'Get product successfully');
    }

    /**
     * CREATE: បង្កើតទំនិញថ្មី
     */
    public function store(Request $request)
    {
        // 🌟 ១. Validation យ៉ាងតឹងរ៉ឹង
        $validatedData = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'name'         => 'required|string|max:255|unique:products,name',
            'sku'          => 'nullable|string|unique:products,sku',
            'description'  => 'nullable|string',
            'is_available' => 'nullable|boolean',
            'is_active'    => 'nullable|boolean',
        ]);

        // 🌟 ២. បង្កើត Slug ដោយស្វ័យប្រវត្តិ
        $validatedData['slug'] = Str::slug($validatedData['name']);

        // ៣. Save ចូល Database
        $product = Product::create($validatedData);

        return $this->createdResponse($product->load('category'), 'Product created successfully');
    }

    /**
     * UPDATE: កែប្រែព័ត៌មានទំនិញ
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->errorResponse('No product found', 404);
        }

        // 🌟 ការពារការ Update ជាន់ឈ្មោះ និង SKU
        $validatedData = $request->validate([
            'category_id'  => 'sometimes|required|exists:categories,id',
            'name'         => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'sku'          => ['nullable', 'string', Rule::unique('products')->ignore($product->id)],
            'description'  => 'nullable|string',
            'is_available' => 'sometimes|boolean',
            'is_active'    => 'sometimes|boolean',
        ]);

        // បើគេកែឈ្មោះ ត្រូវកែ Slug ឱ្យគេដែរ
        if ($request->filled('name')) {
            $validatedData['slug'] = Str::slug($request->name);
        }

        $product->update($validatedData);

        return $this->successResponse($product->load('category'), 'Product updated successfully');
    }

    /**
     * DELETE: លុបទំនិញ (Soft Delete)
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->errorResponse('No product found', 404);
        }

        $product->delete(); // Soft Delete 

        return $this->successResponse(null, 'Product deleted successfully');
    }

    /**
     * POST: បង្ហោះរូបភាពផលិតផល (Upload Image)
     */
    public function uploadImage(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->errorResponse('No product found', 404);
        }

        // ១. ត្រួតពិនិត្យថាវាពិតជារូបភាពមែន និងទំហំមិនលើស 2MB
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // ២. លុបរូបចាស់ចេញពី Storage (ប្រសិនបើធ្លាប់មានរូបពីមុន) ដើម្បីសន្សំទំហំ Server
        if ($product->image_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image_url)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image_url);
        }

        // ៣. រក្សាទុករូបភាពថ្មីចូលទៅក្នុង Folder 'products' នៃ Public Storage
        $path = $request->file('image')->store('products', 'public');

        // ៤. Update ទីតាំងរូបភាពចូលទៅក្នុង Database
        $product->update(['image_url' => $path]);

        // ៥. បោះ Link រូបភាពពេញលេញត្រឡប់ទៅវិញ ដើម្បីឱ្យ Frontend យកទៅបង្ហាញ
        return $this->successResponse(
            ['image_url' => asset('storage/' . $path)],
            'Image uploaded successfully'
        );
    }
}
