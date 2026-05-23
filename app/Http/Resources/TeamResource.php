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
            'id'        => $this->id,
            'name'      => $this->name,
            'created_by'=> $this->created_by,
            'members'   => $this->members->map(function ($member) {
                return [
                    'id'    => $member->id,
                    'name'  => $member->first_name . ' ' . $member->last_name,
                    'email' => $member->email,
                ];
            }),
            'created_at'=> $this->created_at,
        ];
    }
}
