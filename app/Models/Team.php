<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['name','created_by'];
    public function projects()
{
    return $this->belongsToMany(Project::class, 'project_team');
}
public function members()
{
    return $this->belongsToMany(User::class, 'team_members');
}

}
