<?php

namespace App\Http\Resources;

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
            'deadline'    => $this->deadline,
            'assigned_to' => $this->assigned_to,
            'created_by'  => $this->created_by,
            'created_at'  => $this->created_at,
        ];
    }
}
