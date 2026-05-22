<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = [
        'user_id',
        'reward_amount',
        'reward_level',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
