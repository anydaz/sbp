<?php

namespace App\Events;

use App\Models\DeliveryNote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryNoteUpdated
{
    use Dispatchable, SerializesModels;

    public $deliveryNote;
    public $originalDeliveryNote;

    public function __construct(DeliveryNote $deliveryNote, DeliveryNote $originalDeliveryNote)
    {
        $this->deliveryNote = $deliveryNote;
        $this->originalDeliveryNote = $originalDeliveryNote;
    }
}
