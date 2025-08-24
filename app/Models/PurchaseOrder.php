<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

	public $keyType = 'string';

    protected $fillable = ['supplier', 'user_id', 'purchase_discount', 'purchase_number',
	'total', 'shipping_cost', 'shipping_cost_per_item', 'state', 'date', 'payment_category_id', 'down_payment'];

    protected $attributes = ['state' => "active"];

	protected $casts = [
        'purchase_discount' => 'float',
		'total' => 'float',
		'shipping_cost' => 'float',
		'down_payment' => 'float',
		'date' => 'date'
    ];

    /**
 	* Get the user that owns the phone.
 	*/
	public function user()
	{
    	return $this->belongsTo(User::class);
	}

	public function delivery_notes()
	{
    	return $this->hasMany(DeliveryNote::class)->active();
	}

	public function details()
	{
    	return $this->hasMany(PurchaseOrderDetail::class)->active();
	}

	public function payment_category()
	{
		return $this->belongsTo(PaymentCategory::class);
	}

	public function purchase_payments()
	{
		return $this->hasMany(PurchasePayment::class)->active();
	}

	public function scopeActive($query)
	{
		return $query->where('state', 'active');
	}
}
