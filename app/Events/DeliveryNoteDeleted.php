<?php

namespace App\Events;

use App\Models\DeliveryNote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryNoteDeleted
{
    use Dispatchable, SerializesModels;

    public $deliveryNote;

    public function __construct(DeliveryNote $deliveryNote)
    {
        $this->deliveryNote = $deliveryNote;
    }
}
