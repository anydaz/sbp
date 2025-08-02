<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id', 'qty', 'price', 'item_discount', 'subtotal',
        'discount_percentage1', 'discount_percentage2', 'discount_percentage3'
    ];
    protected $attributes = ['state' => "active"];
    protected $casts = [
        'price' => 'float',
        'item_discount' => 'float',
        'subtotal' => 'float'
    ];

    public function product()
	{
    	return $this->belongsTo(Product::class);
	}

    public function delivery_details()
    {
        return $this->hasMany(DeliveryNoteDetail::class)->active();
    }

    public function scopeActive($query)
    {
        return $query->where('state', 'active');
    }
}
