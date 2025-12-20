<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Lấy danh sách sản phẩm (Dùng cho Customer/Staff/Admin).
     */
    public function index(): JsonResponse
    {
        // Lấy tất cả sản phẩm đang available, bao gồm cả metadata phân trang
        $productsPaginator = Product::where('available', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $productsPaginator // Trả về đối tượng Paginator
        ]);
    }

    /**
     * Lấy chi tiết một sản phẩm (Dùng cho Customer/Staff/Admin).
     */
    public function show(Product $product): JsonResponse
    {
        $user = request()->user();

        // Kiểm tra nếu sản phẩm không còn bán hoặc không available
        if (!$product->available) {
            // Nếu KHÔNG phải Admin/Staff, trả về 404
            if (!$user || $user->role === 'customer') {
                return response()->json([
                    'message' => 'Sản phẩm không tồn tại hoặc đã ngừng bán.'
                ], 404);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    // ...
}
