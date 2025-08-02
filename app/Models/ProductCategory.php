<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];
    protected $attributes = ['state' => "active"];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected static function booted()
    {
        static::saving(function ($category) {
            $category->code = strtolower($category->code);
        });
    }

    public function scopeActive($query){
        return $query->where('state', 'active');
    }

}
