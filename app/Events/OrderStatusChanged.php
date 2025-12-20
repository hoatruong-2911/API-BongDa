<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast // ⬅️ IMPLEMENT NÀY
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;

    /**
     * Tạo một phiên bản event mới.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Lấy kênh mà event nên được phát sóng trên đó.
     */
    public function broadcastOn(): Channel
    {
        // Kênh công cộng Staff theo dõi các đơn hàng mới/thay đổi
        return new Channel('orders.updates');
    }

    /**
     * Dữ liệu được phát sóng.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'total' => $this->order->total_amount
        ];
    }
}
