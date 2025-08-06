<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteDetail extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'purchase_order_detail_id', 'received_qty', 'price', 'received_value'];
    protected $attributes = ['state' => "active"];

    public function product()
	{
    	return $this->belongsTo(Product::class);
	}

    public function purchase_order_detail()
	{
    	return $this->belongsTo(PurchaseOrderDetail::class);
	}

    public function scopeActive($query)
    {
        return $query->where('state', 'active');
    }
}
