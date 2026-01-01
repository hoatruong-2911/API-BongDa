<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\Api\Product\ProductRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // Lấy danh sách sản phẩm kèm Brand và Category
    public function index()
    {
        $products = Product::with(['category:id,name', 'brand:id,name'])
            ->latest()
            ->get();

        return response()->json(['data' => $products]);
    }

    // Lưu sản phẩm mới
    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        // Tự động tạo Slug nếu Model chưa xử lý
        $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Đã nhập kho sản phẩm mới rực rỡ!',
            'data' => $product
        ], 201);
    }

    // Xem chi tiết sản phẩm
    public function show($id)
    {
        $product = Product::with(['category', 'brand'])->findOrFail($id);
        return response()->json(['data' => $product]);
    }

    // Cập nhật sản phẩm
    public function update(ProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();

        // $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('image')) {
            // Xóa ảnh cũ
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // Ép kiểu available cho chắc chắn
        $data['available'] = filter_var($request->available, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật sản phẩm thành công!',
            'data' => $product
        ]);
    }

    // Xóa sản phẩm
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return response()->json(['message' => 'Đã xóa sản phẩm khỏi hệ thống']);
    }

    // Đổi trạng thái nhanh
    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->available = !$product->available;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã đổi trạng thái sản phẩm!',
            'available' => $product->available
        ]);
    }
}
