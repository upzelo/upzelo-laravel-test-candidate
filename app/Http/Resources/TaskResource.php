<?php

namespace App\Http\Resources;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request): array
    {

        return [
            'id'          => $this->id,
            'title'        => $this->title,
            'description' => $this->description,
            'status'     => $this->status,
            'priority'      => $this->priority,
            'project_id' => $this->project_id,
            'assigned_to' => $this->assigned_to,
            'due_date' => $this->due_date,
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
