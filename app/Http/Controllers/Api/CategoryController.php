<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Category\CategoryRequest; // Giả định bro đã tạo Request này
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Lấy danh sách cho Admin
     * Sắp xếp theo thứ tự ưu tiên (sort_order)
     */
    public function index()
    {
        $categories = Category::withCount('products')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['data' => $categories]);
    }

    /**
     * Lưu danh mục mới
     */
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        // Xử lý upload ảnh danh mục
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = $path;
        }

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm danh mục rực rỡ!',
            'data' => $category
        ], 201);
    }

    /**
     * Lấy chi tiết danh mục kèm 5 sản phẩm mới nhất
     */
    public function show($id)
    {
        $category = Category::withCount('products')
            ->with(['products' => function ($query) {
                $query->latest()->limit(5);
            }])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Cập nhật danh mục
     */
    public function update(CategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu tồn tại
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            // Lưu ảnh mới
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        // Đồng bộ trạng thái từ Switch (true/false -> 1/0)
        $data['is_active'] = $request->is_active ? 1 : 0;

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật danh mục thành công rực rỡ!',
            'data' => $category
        ]);
    }

    /**
     * Xóa danh mục
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();
        return response()->json(['message' => 'Đã xóa danh mục']);
    }

    /**
     * Đổi trạng thái nhanh (Toggle)
     */
    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã đổi trạng thái danh mục rực rỡ!',
            'is_active' => $category->is_active
        ]);
    }
}
