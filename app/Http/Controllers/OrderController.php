<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // =========================================================================
    // ១. ផ្នែកសម្រាប់អតិថិជន (Customer Flow)
    // =========================================================================

    /**
     * CHECKOUT: បញ្ជាក់ការបញ្ជាទិញ (ប្រែក្លាយ Cart ទៅជា Order)
     */
    public function checkout(Request $request)
    {
        $user = $request->user();

        // ១. ទាញយកកន្ត្រក និងទំនិញ
        $cart = Cart::with(['items.product', 'items.productSize'])->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return $this->errorResponse('កន្ត្រកទំនិញរបស់អ្នកទទេស្អាត សូមជ្រើសរើសទំនិញជាមុនសិន', 400);
        }

        $request->validate([
            'order_type' => 'required|in:dine_in,takeaway,delivery',
            'note'       => 'nullable|string|max:500'
        ]);

        try {
            // 🌟 ប្រើប្រាស់ DB Transaction ដើម្បីការពារទិន្នន័យបាត់បង់ពេលមានបញ្ហា Error
            DB::beginTransaction();

            $totalAmount = 0;
            $orderItemsData = [];

            // ២. គណនាតម្លៃ និងរៀបចំទិន្នន័យ Snapshot
            foreach ($cart->items as $item) {
                // យកតម្លៃពី Size (ប្រសិនបើមាន)
                $unitPrice = $item->productSize ? $item->productSize->price : 0;
                $subtotal = $unitPrice * $item->quantity;
                $totalAmount += $subtotal;

                // បំប្លែង Modifiers (JSON) ទៅជាអក្សរធម្មតាដើម្បីដាក់ចូល Note វិក្កយបត្រ
                $modifierText = $item->modifiers_json ? json_encode($item->modifiers_json, JSON_UNESCAPED_UNICODE) : null;

                $orderItemsData[] = [
                    'product_id'      => $item->product_id,
                    'product_size_id' => $item->product_size_id,
                    'product_name'    => $item->product->name, // 🌟 Snapshot Name
                    'size_name'       => $item->productSize ? $item->productSize->name : null, // 🌟 Snapshot Size
                    'quantity'        => $item->quantity,
                    'unit_price'      => $unitPrice,           // 🌟 Snapshot Price
                    'subtotal'        => $subtotal,
                    'note'            => $modifierText,
                ];
            }

            // ៣. បង្កើតវិក្កយបត្រ (Order)
            $order = Order::create([
                'order_number'    => 'INV-' . strtoupper(Str::random(8)), // ឧទាហរណ៍: INV-A1B2C3D4
                'user_id'         => $user->id,
                'total_amount'    => $totalAmount,
                'discount_amount' => 0, // អាចបន្ថែមប្រព័ន្ធ Discount ក្រោយ
                'tax_amount'      => 0,
                'net_amount'      => $totalAmount, // net = total - discount + tax
                'status'          => 'pending',
                'order_type'      => $request->order_type,
                'payment_status'  => 'unpaid',
                'note'            => $request->note,
            ]);

            // ៤. បញ្ចូលទំនិញទៅក្នុងវិក្កយបត្រ (Order Items)
            $order->items()->createMany($orderItemsData);

            // ៥. សម្អាតកន្ត្រកទំនិញចោល
            $cart->items()->delete();

            DB::commit(); // យល់ព្រមរក្សាទុកទិន្នន័យទាំងអស់

            return $this->createdResponse($order->load('items'), 'ការបញ្ជាទិញទទួលបានជោគជ័យ');
        } catch (\Exception $e) {
            DB::rollBack(); // បើមាន Error ទាត់ចោលកូដខាងលើទាំងអស់ (ការពារលុយគិតហើយ តែអត់ Order)
            return $this->errorResponse('មានបញ្ហាក្នុងការបញ្ជាទិញ សូមព្យាយាមម្តងទៀត', 500);
        }
    }

    /**
     * READ: អតិថិជនមើលប្រវត្តិទិញរបស់ខ្លួនឯង
     */
    public function myOrders(Request $request)
    {
        $orders = Order::with('items')->where('user_id', $request->user()->id)->orderBy('id', 'desc')->paginate(10);
        return $this->successResponse($orders, 'ទាញយកប្រវត្តិបញ្ជាទិញជោគជ័យ');
    }

    // =========================================================================
    // ២. ផ្នែកសម្រាប់អ្នកគ្រប់គ្រង (Admin / Staff Flow)
    // =========================================================================

    /**
     * READ ALL: Admin មើលវិក្កយបត្រទាំងអស់
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']);

        // Filter តាម Status បើចាំបាច់
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('id', 'desc')->paginate(15);
        return $this->successResponse($orders, 'ទាញយកបញ្ជីវិក្កយបត្រជោគជ័យ');
    }

    /**
     * UPDATE STATUS: Admin ដូរស្ថានភាព (ឧ. ពី pending ទៅ preparing ឬ paid)
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return $this->errorResponse('រកមិនឃើញវិក្កយបត្រនេះទេ', 404);
        }

        $request->validate([
            'status'         => 'sometimes|in:pending,preparing,ready,completed,cancelled',
            'payment_status' => 'sometimes|in:unpaid,paid,refunded',
        ]);

        $order->update($request->only(['status', 'payment_status']));

        return $this->successResponse($order, 'ស្ថានភាពវិក្កយបត្រត្រូវបានកែប្រែជោគជ័យ');
    }
}