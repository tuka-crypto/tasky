<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,
            'priority'    => $this->priority,
            'start_date'  => $this->start_date,
            'end_date'    => $this->end_date,
            'is_approved' => $this->is_approved,
            'is_personal' => $this->project_id === null,
            'project' => $this->project
    ? [
        'id' => $this->project->id,
        'title' => $this->project->title,
    ]
    : null,
            'members'     => $this->members->map(function ($m) {
                return [
                    'id'    => $m->id,
                    'name'  => $m->first_name . ' ' . $m->last_name,
                    'email' => $m->email,
                ];
            }),
            'tags'        => $this->tags->pluck('name'),
            'attachments' => $this->attachments->map(function ($a) {
                return [
                    'id'        => $a->id,
                    'file_name' => $a->file_name,
                    'file_path' => $a->file_path,
                ];
            }),
            'dependencies' => $this->dependencies->pluck('depends_on_task_id'),
            'history'      => $this->history->map(function ($h) {
                return [
                    'action'     => $h->action,
                    'user_id'    => $h->user_id,
                    'created_at' => $h->created_at,
                ];
            }),
        ];
    }
}

