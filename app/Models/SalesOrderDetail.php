<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderDetail extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'qty', 'price', 'cogs', 'item_discount', 'subtotal'];
    protected $attributes = ['state' => "active"];
    protected $casts = [
        'price' => 'float',
        'cogs' => 'float',
        'item_discount' => 'float',
        'subtotal' => 'float',
    ];

    public function product()
	{
    	return $this->belongsTo(Product::class);
	}

    public function scopeActive($query)
    {
        return $query->where('state', 'active');
    }
}
