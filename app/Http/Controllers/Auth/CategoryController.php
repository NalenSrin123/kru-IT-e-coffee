
<?php



use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

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

        return CategoryResource::collection($categories)->additional([
            'success' => true,
            'message' => 'Categories retrieved successfully',
        ]);
    }

    /**
     * POST /api/categories
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data'    => new CategoryResource($category),
        ], 201);
    }

    /**
     * GET /api/categories/{category}
     */
    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data'    => new CategoryResource($category),
        ]);
    }

    /**
     * PUT /api/categories/{category}
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data'    => new CategoryResource($category),
        ]);
    }

    /**
     * DELETE /api/categories/{category}
     */
    public function destroy(Category $category)
    {
        $category->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    /**
     * GET /api/categories/trashed
     */
    public function trashed()
    {
        $categories = Category::onlyTrashed()->paginate(10);

        return CategoryResource::collection($categories)->additional([
            'success' => true,
            'message' => 'Trashed categories retrieved',
        ]);
    }

    /**
     * POST /api/categories/{id}/restore
     */
    public function restore($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();

        return response()->json([
            'success' => true,
            'message' => 'Category restored successfully',
            'data'    => new CategoryResource($category),
        ]);
    }

    /**
     * DELETE /api/categories/{id}/force
     */
    public function forceDelete($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Category permanently deleted',
        ]);
    }
}