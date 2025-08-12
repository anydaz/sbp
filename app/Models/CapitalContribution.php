<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapitalContribution extends Model
{
    protected $fillable = [
        'date',
        'amount',
        'notes'
    ];

    protected $attributes = ['state' => "active"];

    protected $casts = [
        'date' => 'date',
        'amount' => 'float'
    ];
}
