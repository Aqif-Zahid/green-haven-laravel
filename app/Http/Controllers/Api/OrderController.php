<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        if ($product->quantity < $data['quantity']) {
            return ApiResponse::error('Not enough product quantity available.', 400);
        }

        $totalPrice = $product->price * $data['quantity'];

        $order = Order::create([
            'user_id' => $request->user()->id,
            'total_amount' => $totalPrice,
            'status' => 'PENDING',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $data['quantity'],
            'total_price' => $totalPrice,
        ]);

        $product->decrement('quantity', $data['quantity']);

        return ApiResponse::success('Order created successfully.', [
            'order_id' => $order->id,
        ], 201);
    }

    public function index(Request $request)
    {
        $orders = Order::with(['items.product'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return ApiResponse::success('Orders fetched successfully.', $orders);
    }

    public function checkout(Request $request, string $order)
    {
        $data = $request->validate([
            'delivery_address' => ['required', 'string', 'max:500'],
        ]);

        $orderModel = Order::where('id', $order)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$orderModel) {
            return ApiResponse::error('Order not found.', 404);
        }

        $orderModel->update([
            'delivery_address' => $data['delivery_address'],
            'payment_method' => 'COD',
            'payment_status' => 'UNPAID',
        ]);

        return ApiResponse::success('Checkout completed (Cash on Delivery).', [
            'order_id' => $orderModel->id,
        ]);
    }
}
