<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskDependency extends Model
{
    protected $fillable = ['task_id', 'depends_on_task_id', 'type'];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function dependsOn()
    {
        return $this->belongsTo(Task::class, 'depends_on_task_id');
    }
}
