<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;

	public $keyType = 'string';
    protected $fillable = ['supplier', 'user_id'];
    protected $attributes = ['state' => "active"];

    /**
 	* Get the user that owns the phone.
 	*/
	public function user()
	{
    	return $this->belongsTo(User::class);
	}

	public function details()
	{
    	return $this->hasMany(PurchaseReturnDetail::class)->active();
	}

	public function scopeActive($query)
	{
		return $query->where('state', 'active');
	}
}
