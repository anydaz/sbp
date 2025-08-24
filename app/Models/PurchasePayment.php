<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    use HasFactory;

	public $keyType = 'string';

    protected $fillable = ['purchase_order_id', 'amount', 'payment_date'];

    protected $attributes = ['state' => "active"];

	protected $casts = [
        'amount' => 'float',
    ];

	public function purchase_order()
	{
    	return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
	}

	public function purchaseOrder()
	{
    	return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
	}

	public function scopeActive($query)
	{
		return $query->where('state', 'active');
	}
}
