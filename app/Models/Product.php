<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'price', 'quantity', 'cogs', 'last_edited', 'product_category_id'];
    protected $attributes = ['state' => "active"];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'last_edited',
    ];
    protected $casts = [
        'price' => 'float',
        'cogs' => 'float'
    ];

    public function scopeActive($query){
        return $query->where('state', 'active');
    }

    public function category()
	{
    	return $this->belongsTo(ProductCategory::class, 'product_category_id');
	}

    public function logs()
    {
        return $this->hasMany(ProductLog::class);
    }
}
