<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
// use App\Http\Requests\Api\Admin\ContactRequest;
use App\Http\Requests\Api\Admin\ContactRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    /**
     * KHÁCH HÀNG: Gửi liên hệ mới
     */
    public function store(ContactRequest $request): JsonResponse
    {
        try {
            $contact = Contact::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Gửi liên hệ thành công! Chúng tôi sẽ phản hồi sớm nhất.',
                'data'    => $contact
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ADMIN: Lấy danh sách (Có lọc trạng thái & phân trang)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Contact::query();
        
        // Lọc theo trạng thái nếu có truyền status lên (0, 1, 2)
        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        $contacts = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return response()->json([
            'success' => true, 
            'data' => $contacts
        ]);
    }

    /**
     * ADMIN: Xem chi tiết liên hệ
     */
    public function show($id): JsonResponse
    {
        $contact = Contact::find($id);
        if (!$contact) return response()->json(['success' => false, 'message' => 'Không tìm thấy!'], 404);

        // Tự động đánh dấu là Đã đọc (1) nếu đang ở trạng thái Chưa đọc (0)
        if ($contact->status === 0) {
            $contact->update(['status' => 1]);
        }

        return response()->json(['success' => true, 'data' => $contact]);
    }

    /**
     * ADMIN: Cập nhật trạng thái & Ghi chú
     */
    public function update(ContactRequest $request, $id): JsonResponse
    {
        $contact = Contact::find($id);
        if (!$contact) return response()->json(['success' => false, 'message' => 'Không tìm thấy!'], 404);

        $contact->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật liên hệ thành công!',
            'data'    => $contact
        ]);
    }

    /**
     * ADMIN: Thống kê nhanh số lượng
     */
    public function getStats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total'      => Contact::count(),
                'unread'     => Contact::where('status', 0)->count(),
                'processing' => Contact::where('status', 1)->count(),
                'completed'  => Contact::where('status', 2)->count(),
            ]
        ]);
    }

    /**
     * ADMIN: Cập nhật trạng thái hàng loạt
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids'    => 'required|array',
            'status' => 'required|integer|in:0,1,2'
        ]);

        Contact::whereIn('id', $request->ids)->update(['status' => $request->status]);

        return response()->json([
            'success' => true, 
            'message' => 'Đã cập nhật trạng thái hàng loạt thành công!'
        ]);
    }

    /**
     * ADMIN: Xóa liên hệ
     */
    public function destroy($id): JsonResponse
    {
        $contact = Contact::find($id);
        if (!$contact) return response()->json(['success' => false, 'message' => 'Không tìm thấy!'], 404);

        $contact->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa liên hệ rực rỡ!']);
    }

    /**
     * ADMIN: Xóa hàng loạt
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array'
        ]);

        Contact::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true, 
            'message' => 'Đã dọn dẹp các liên hệ được chọn!'
        ]);
    }
}