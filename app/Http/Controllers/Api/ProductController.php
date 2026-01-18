<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class ProductController extends Controller
{
    private function normalizeImages($images): array
    {
        $result = [];

        foreach ((array) ($images ?? []) as $img) {
            if (!is_string($img)) {
                continue;
            }

            $v = trim($img);
            if ($v === '') {
                continue;
            }

            if (str_starts_with($v, '[') && str_ends_with($v, ']')) {
                $decoded = json_decode($v, true);

                if (is_array($decoded)) {
                    foreach ($decoded as $d) {
                        if (is_string($d) && trim($d) !== '') {
                            $result[] = trim($d);
                        }
                    }
                    continue;
                }
            }

            $result[] = $v;
        }

        // Remove dummy values
        $result = array_values(array_filter($result, function ($s) {
            $v = strtolower(trim($s));
            if ($v === '') return false;
            if (str_ends_with($v, 'test.jpg')) return false;
            return true;
        }));

        return $result;
    }

    public function index(Request $request)
    {
        $category = $request->query('category');

        $query = Product::with('user')->latest();

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $products = $query->paginate(20);

        $products->getCollection()->transform(function ($product) {
            $product->images = $this->normalizeImages($product->images);
            return $product;
        });

        return ApiResponse::success('Products fetched successfully.', $products);
    }

    public function show(string $id)
    {
        $product = Product::with('user')->find($id);

        if (!$product) {
            return ApiResponse::error('Product not found.', 404);
        }

        $product->images = $this->normalizeImages($product->images);

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

        $product->images = $this->normalizeImages($product->images);

        return ApiResponse::success('Product created successfully.', $product, 201);
    }
}
