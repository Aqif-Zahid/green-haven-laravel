<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');

        $query = Product::with('user')->latest();

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $products = $query->paginate(20);

        return ApiResponse::success('Products fetched successfully.', $products);
    }

    public function show(string $id)
    {
        $product = Product::with('user')->find($id);

        if (!$product) {
            return ApiResponse::error('Product not found.', 404);
        }

        return ApiResponse::success('Product fetched successfully.', $product);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'small_description' => ['required', 'string', 'max:500'],
            'description' => ['nullable'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['string'],
            'category' => ['required', 'in:plants,accessories,flowers'],
        ]);

        $product = Product::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return ApiResponse::success('Product created successfully.', $product, 201);
    }
}
