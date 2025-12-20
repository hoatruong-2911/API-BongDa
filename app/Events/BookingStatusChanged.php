<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; 
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingStatusChanged implements ShouldBroadcast // ⬅️ IMPLEMENT NÀY
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function broadcastOn(): Channel
    {
        // Kênh công cộng Staff/Admin theo dõi
        return new Channel('bookings.updates');
    }
    
    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->booking->id,
            'status' => $this->booking->status,
            'field_id' => $this->booking->field_id
        ];
    }
}