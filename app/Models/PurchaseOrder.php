<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

	public $keyType = 'string';
    protected $fillable = ['supplier', 'user_id', 'purchase_discount', 'purchase_number', 'total', 'shipping_cost'];
    protected $attributes = ['state' => "active"];

	protected $casts = [
        'purchase_discount' => 'float',
		'shipping_cost' => 'float',
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

	public function scopeActive($query)
	{
		return $query->where('state', 'active');
	}
}
