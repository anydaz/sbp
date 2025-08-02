<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DraftSalesOrder extends Model
{
    use HasFactory;

	public $keyType = 'string';
    protected $fillable = ['customer_id', 'user_id'];
    protected $attributes = ['state' => "active"];

    /**
 	* Get the user that owns the phone.
 	*/
	public function user()
	{
    	return $this->belongsTo(User::class);
	}

	public function customer()
	{
    	return $this->belongsTo(Customer::class);
	}

	public function sales_order()
	{
    	return $this->hasOne(SalesOrder::class);
	}

	public function details()
	{
    	return $this->hasMany(DraftSalesOrderDetail::class)->active();
	}

	public function scopeActive($query){
		return $query->where('state', 'active');
	}
}
