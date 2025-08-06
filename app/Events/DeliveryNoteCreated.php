<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\DeliveryNote;

class DeliveryNoteCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $deliveryNote;

    public function __construct(DeliveryNote $deliveryNote)
    {
        $this->deliveryNote = $deliveryNote;
    }
}
