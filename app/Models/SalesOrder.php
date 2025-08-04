<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

	public $keyType = 'string';
    protected $fillable = ['customer_id', 'payment_category_id', 'user_id', 'sales_discount', 'draft_sales_order_id',
						   'sales_number', 'payment_type_id', 'is_pending', 'notes', 'date',
						   'total_return', 'total'];

    protected $attributes = ['state' => "active"];

	protected $casts = [
        'sales_discount' => 'float',
		'total_return' => 'float',
    ];

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

	public function draft()
	{
    	return $this->belongsTo(DraftSalesOrder::class, 'draft_sales_order_id');
	}

	public function details()
	{
    	return $this->hasMany(SalesOrderDetail::class)->active();
	}

	public function payment_type()
	{
    	return $this->belongsTo(PaymentType::class)->active();
	}

	public function payment_category()
	{
    	return $this->belongsTo(PaymentCategory::class);
	}

	public function scopeActive($query)
	{
		return $query->where('state', 'active');
	}
}
