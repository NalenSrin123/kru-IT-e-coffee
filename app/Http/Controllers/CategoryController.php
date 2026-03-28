<?php

namespace App\Http\Controllers; // កុំភ្លេច Namespace

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class CategoryController extends Controller
{
    /**
     * GET /api/categories
     */
    public function index(Request $request)
    {
        $query = Category::query();

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sorting
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $categories = $query->paginate($request->get('per_page', 10));

        // 🌟 កែសម្រួល៖ រក្សាទម្រង់ Pagination របស់ Laravel រួចបោះចូលស្តង់ដារ API របស់យើង
        $paginatedData = CategoryResource::collection($categories)->response()->getData(true);

        return $this->successResponse($paginatedData, 'Categories retrieved successfully');
    }

    /**
     * POST /api/categories
     */
    public function store(CategoryRequest $request)
    {
        // ១. ចាប់យកទិន្នន័យដែលបានឆ្លងកាត់ការត្រួតពិនិត្យ (Valid) រួច
        $validatedData = $request->validated();

        // ២. បង្កើត Slug ដោយស្វ័យប្រវត្តិ ដោយផ្អែកលើឈ្មោះ (Name)
        $validatedData['slug'] = Str::slug($validatedData['name']);

        // ៣. បង្កើត Category ថ្មី
        $category = Category::create($validatedData);

        // ៤. បោះលទ្ធផលត្រឡប់ទៅវិញ
        return $this->createdResponse(
            new CategoryResource($category),
            'Category created successfully'
        );
    }

    /**
     * GET /api/categories/{category}
     */
    public function show(Category $category)
    {
        // 🌟 កែសម្រួល៖ ប្រើ successResponse
        return $this->successResponse(
            new CategoryResource($category),
            'Category retrieved successfully'
        );
    }

    /**
     * PUT /api/categories/{category}
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $validatedData = $request->validated();

        // 🌟 ធ្វើ Auto-Slug សម្រាប់ពេល Update ដែរ (បើគេដូរឈ្មោះ យើងដូរ Slug តាមគេ)
        if (isset($validatedData['name'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        }

        $category->update($validatedData);

        return $this->successResponse(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    /**
     * DELETE /api/categories/{category}
     */
    public function destroy(Category $category)
    {
        $category->delete(); // Soft delete

        // 🌟 កែសម្រួល៖ ប្រើ successResponse (null ព្រោះអត់មាន Data ត្រូវបោះទៅវិញ)
        return $this->successResponse(null, 'Category deleted successfully');
    }

    /**
     * GET /api/categories/trashed
     */
    public function trashed()
    {
        $categories = Category::onlyTrashed()->paginate(10);
        $paginatedData = CategoryResource::collection($categories)->response()->getData(true);

        return $this->successResponse($paginatedData, 'Trashed categories retrieved');
    }

    /**
     * POST /api/categories/{id}/restore
     */
    public function restore($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();

        return $this->successResponse(
            new CategoryResource($category),
            'Category restored successfully'
        );
    }

    /**
     * DELETE /api/categories/{id}/force
     */
    public function forceDelete($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->forceDelete();

        return $this->successResponse(null, 'Category permanently deleted');
    }

    /**
     * POST /api/categories/{id}/image
     * សម្រាប់បង្ហោះរូបភាពតំណាង Category
     */
    public function uploadImage(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->errorResponse('រកមិនឃើញ Category នេះទេ។', 404);
        }

        // ១. Validate រូបភាព
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // ២. លុបរូបចាស់ចោល (បើមាន)
        if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
            Storage::disk('public')->delete($category->image_url);
        }

        // ៣. រក្សាទុករូបថ្មី
        $path = $request->file('image')->store('categories', 'public');

        // ៤. Update Database
        $category->update(['image_url' => $path]);

        // ៥. បោះលទ្ធផលត្រឡប់ទៅវិញ
        return $this->successResponse(
            ['image_url' => asset('storage/' . $path)],
            'រូបភាព Category ត្រូវបានផ្លាស់ប្តូរជោគជ័យ'
        );
    }
}
