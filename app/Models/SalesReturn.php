<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;

	public $keyType = 'string';
    protected $fillable = ['customer', 'user_id'];
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
    	return $this->hasMany(SalesReturnDetail::class)->active();
	}

	public function scopeActive($query)
	{
		return $query->where('state', 'active');
	}
}
