<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function tasks()
    {
    return $this->hasMany(Task::class);
    }
    public function teams()
    {
    return $this->belongsToMany(Team::class, 'project_team');
    }
    public function history()
{
    return $this->hasMany(ProjectHistory::class);
}

}
