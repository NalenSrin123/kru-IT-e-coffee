<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * READ: មើលកន្ត្រកទំនិញរបស់ខ្លួនឯង (Get My Cart)
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // ស្វែងរកកន្ត្រករបស់គាត់ បើគ្មានទេ បង្កើតថ្មីឱ្យគាត់តែម្តង (firstOrCreate)
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        // ទាញយក Items ទាំងអស់ ដោយភ្ជាប់មកជាមួយព័ត៌មាន Product និង Size
        $cart->load(['items.product', 'items.productSize']);

        return $this->successResponse($cart, 'ទាញយកកន្ត្រកទំនិញជោគជ័យ');
    }

    /**
     * CREATE / UPDATE: បន្ថែមទំនិញចូលកន្ត្រក (Add to Cart)
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id'      => 'required|exists:products,id',
            'product_size_id' => 'nullable|exists:product_sizes,id',
            'quantity'        => 'required|integer|min:1',
            'modifiers_json'  => 'nullable|array',
        ]);

        $userId = $request->user()->id;
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        // ឆែកមើលថាតើទំនិញដែលមាន Size និង Modifiers ដូចគ្នានេះ មានក្នុងកន្ត្រករួចហើយឬនៅ?
        // ឆែកមើលថាតើទំនិញដែលមាន Size និង Modifiers ដូចគ្នានេះ មានក្នុងកន្ត្រករួចហើយឬនៅ?
        $query = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->where('product_size_id', $request->product_size_id);

        // ដោះស្រាយបញ្ហា PostgreSQL យ៉ាងវៃឆ្លាត
        if (empty($request->modifiers_json)) {
            // បើគ្មាន Modifier ទេ ប្រើ whereNull (ជំនួសឱ្យការប្រើ = null)
            $query->whereNull('modifiers_json');
        } else {
            // បើមាន Modifier ត្រូវ Cast កូឡោន JSON នោះទៅជា TEXT សិន ទើប PostgreSQL ព្រមប្រៀបធៀប
            $query->whereRaw('CAST(modifiers_json AS TEXT) = ?', [json_encode($request->modifiers_json)]);
        }

        $existingItem = $query->first();

        if ($existingItem) {
            // បើមានហើយ គ្រាន់តែបូកចំនួនបន្ថែម (Quantity)
            $existingItem->update([
                'quantity' => $existingItem->quantity + $request->quantity
            ]);
            $message = 'បានបន្ថែមចំនួនទំនិញទៅក្នុងកន្ត្រក';
        } else {
            // បើមិនទាន់មាន បង្កើត Item ថ្មី
            $cart->items()->create([
                'product_id'      => $request->product_id,
                'product_size_id' => $request->product_size_id,
                'quantity'        => $request->quantity,
                'modifiers_json'  => empty($request->modifiers_json) ? null : $request->modifiers_json,
            ]);
            $message = 'បានបន្ថែមទំនិញថ្មីចូលកន្ត្រក';
        }

        // ត្រឡប់កន្ត្រកដែលអាប់ដេតរួចទៅឱ្យ Frontend
        return $this->successResponse($cart->fresh()->load('items.product', 'items.productSize'), $message);
    }

    /**
     * UPDATE: កែប្រែចំនួនទំនិញក្នុងកន្ត្រក (Update Quantity)
     */
    public function update(Request $request, $itemId)
    {
        $userId = $request->user()->id;

        // ស្វែងរក Item នោះ ដោយធានាថាវាពិតជានៅក្នុងកន្ត្រករបស់គាត់មែន
        $cartItem = CartItem::whereHas('cart', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->find($itemId);

        if (!$cartItem) {
            return $this->errorResponse('រកមិនឃើញទំនិញនេះក្នុងកន្ត្រករបស់អ្នកទេ', 404);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1' // កំណត់យ៉ាងតិច ១ បើចង់លុប ត្រូវហៅ API លុប
        ]);

        $cartItem->update(['quantity' => $request->quantity]);

        return $this->successResponse($cartItem, 'ចំនួនទំនិញត្រូវបានកែប្រែជោគជ័យ');
    }

    /**
     * DELETE: ដកទំនិញណាមួយចេញពីកន្ត្រក (Remove from Cart)
     */
    public function destroy(Request $request, $itemId)
    {
        $userId = $request->user()->id;

        $cartItem = CartItem::whereHas('cart', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->find($itemId);

        if (!$cartItem) {
            return $this->errorResponse('រកមិនឃើញទំនិញនេះក្នុងកន្ត្រករបស់អ្នកទេ', 404);
        }

        $cartItem->delete();

        return $this->successResponse(null, 'ទំនិញត្រូវបានដកចេញពីកន្ត្រកជោគជ័យ');
    }
}
