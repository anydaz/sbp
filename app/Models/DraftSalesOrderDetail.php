<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DraftSalesOrderDetail extends Model
{
    use HasFactory;

    public $keyType = 'string';
    protected $fillable = ['product_id', 'qty', 'price', 'subtotal'];
    protected $attributes = ['state' => "active"];
    protected $casts = [
        'price' => 'float',
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
