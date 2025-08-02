<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];
    protected $attributes = ['state' => "active"];

    public function scopeActive($query){
        return $query->where('state', 'active');
    }
}
