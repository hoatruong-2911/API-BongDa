<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Brand\BrandRequest as BrandRequest;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * Lấy danh sách cho Admin
     * Đã cập nhật orderBy để sort_order hoạt động chuẩn xác
     */
    public function index()
    {
        $brands = Brand::withCount('products')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $brands]);
    }

    /**
     * Lưu thương hiệu mới
     * Thay Request $request bằng BrandRequest $request
     */
    public function store(BrandRequest $request)
    {
        // Lấy dữ liệu đã được validate
        $data = $request->validated();

        // Xử lý upload Logo (nếu có)
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('brands', 'public');
            $data['logo'] = $path;
        }

        // Tạo Brand mới (Slug tự động tạo ở Model)
        $brand = Brand::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm thương hiệu rực rỡ!',
            'data' => $brand
        ], 201);
    }

    /**
     * Lấy chi tiết thương hiệu
     */
    public function show($id)
    {
        // Lấy Brand kèm theo đếm số lượng SP và 5 sản phẩm gần nhất
        $brand = Brand::withCount('products')
            ->with(['products' => function ($query) {
                $query->latest()->limit(5);
            }])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $brand
        ]);
    }

    /**
     * Cập nhật thương hiệu
     * Thay Request $request bằng BrandRequest $request
     */
    public function update(BrandRequest $request, $id)
    {
        $brand = Brand::findOrFail($id);

        // Lấy dữ liệu đã được validate
        $data = $request->validated();

        // Xử lý logic ảnh
        if ($request->hasFile('logo')) {
            // 1. Xóa logo cũ trong storage nếu tồn tại
            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }
            // 2. Lưu logo mới
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        // Convert is_active về 0/1 cho database (do Antd Switch gửi boolean)
        $data['is_active'] = $request->is_active ? 1 : 0;

        $brand->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thương hiệu thành công rực rỡ!',
            'data' => $brand
        ]);
    }

    /**
     * Xóa thương hiệu
     */
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);

        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();
        return response()->json(['message' => 'Đã xóa thương hiệu']);
    }

    public function toggleStatus($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->is_active = !$brand->is_active;
        $brand->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã đổi trạng thái rực rỡ!',
            'is_active' => $brand->is_active
        ]);
    }
}
