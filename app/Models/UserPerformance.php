<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPerformance extends Model
{
    protected $table = 'user_performance';

    protected $fillable = [
        'user_id',
        'completed_tasks',
        'late_tasks',
        'total_tasks',
        'performance_score',
        'streak_days',
        'calculation_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
