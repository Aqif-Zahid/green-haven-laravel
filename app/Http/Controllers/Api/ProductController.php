<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\IndexProductsRequest;
use App\Http\Requests\Product\ShowProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Models\Product;
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

    public function index(IndexProductsRequest $request)
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

    public function show(ShowProductRequest $request)
    {
        $id = (string) $request->validated('id');

        $product = Product::with('user')->find($id);

        $product->images = $this->normalizeImages($product->images);

        return ApiResponse::success('Product fetched successfully.', $product);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        $product = Product::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        $product->images = $this->normalizeImages($product->images);

        return ApiResponse::success('Product created successfully.', $product, 201);
    }
}
