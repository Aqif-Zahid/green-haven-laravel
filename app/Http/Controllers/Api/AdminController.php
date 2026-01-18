<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::where('email', $data['email'])->first();

        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            return ApiResponse::error('Invalid admin credentials.', 401);
        }

        $token = $admin->createToken('admin')->plainTextToken;

        return ApiResponse::success('Admin logged in successfully.', [
            'token' => $token,
        ]);
    }

    public function products()
    {
        $products = Product::with('user')->latest()->get();

        return ApiResponse::success('All products fetched successfully.', $products);
    }

    public function deleteProduct(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error('Product not found.', 404);
        }

        $product->delete();

        return ApiResponse::success('Product deleted successfully.');
    }
}
