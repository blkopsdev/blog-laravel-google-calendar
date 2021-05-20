<?php

namespace App;

use App\Services\Google;
use App\User;
use Illuminate\Database\Eloquent\Model;

class GoogleAccount extends Model
{

    protected $fillable = [
        'google_id', 'name', 'email', 'token',
    ];

    protected $casts = [
        'token' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
