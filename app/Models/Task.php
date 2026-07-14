<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'start_date',
        'end_date',
        'is_approved',
        'project_id',
        'created_by',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function members()
    {
    return $this->belongsToMany(User::class, 'task_members');
    }
    public function dependencies()
{
    return $this->hasMany(TaskDependency::class, 'task_id');
}

public function blockedBy()
{
    return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
}

public function attachments()
{
    return $this->hasMany(TaskAttachment::class);
}
public function history()
{
    return $this->hasMany(TaskHistory::class);
}
protected $casts=[
'start_date'=>'date',
'end_date'=>'date',
'is_approved'=>'boolean'
];
public function owner()
{
    return $this->belongsTo(User::class,'owner_id');
}
}
