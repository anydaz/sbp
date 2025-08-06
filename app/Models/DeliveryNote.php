<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    use HasFactory;

	public $keyType = 'string';
    protected $fillable = ['purchase_order_id', 'user_id', 'total'];
    protected $attributes = ['state' => "active"];

    /**
 	* Get the user that owns the phone.
 	*/
	public function purchase_order()
	{
    	return $this->belongsTo(PurchaseOrder::class);
	}

	public function details()
	{
    	return $this->hasMany(DeliveryNoteDetail::class)->active();
	}

	public function scopeActive($query)
	{
		return $query->where('state', 'active');
	}
}
