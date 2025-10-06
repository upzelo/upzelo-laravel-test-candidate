<?php

namespace App\Http\Resources;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {

        return [
             'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'user_id'     => $this->user_id,
            'status'      => $this->status,
            'tasks_count' => $this->tasks_count,
            'completed_percentage' => $this->getCompletionPercentageAttribute(),
            'created_at'  => $this->created_at?->toDateTimeString(),
            'updated_at'  => $this->updated_at?->toDateTimeString(),
        ];
    }

    public function with($request): array
    {
        return [
            'status' => 'success',
        ];
    }
}
