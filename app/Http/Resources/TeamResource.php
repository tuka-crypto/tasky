<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,

            'created_by' => $this->created_by,

            'members' => $this->whenLoaded('members', function () {
                return $this->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->first_name . ' ' . $member->last_name,
                        'email' => $member->email,
                    ];
                });
            }),
            'projects' => $this->whenLoaded('projects', function () {
                return $this->projects->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'title' => $project->title,
                        'description' => $project->description,
                        'start_date' => $project->start_date,
                        'end_date' => $project->end_date,
        ];
    });
}),
            'created_at' => $this->created_at,
        ];
    }
}
