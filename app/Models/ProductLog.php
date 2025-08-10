<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductLog extends Model
{
    protected $fillable = [
        'product_id',
        'action',
        'qty_before',
        'qty_after',
        'price',
        'cogs_before',
        'cogs_after',
        'note',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
