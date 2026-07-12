<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamListResource extends JsonResource
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

            'members' => $this->members->map(function ($member) {
                return $member->first_name . ' ' . $member->last_name;
            }),

            'projects_count' => $this->projects->count(),

            'created_at' => $this->created_at,
        ];
    }
}
