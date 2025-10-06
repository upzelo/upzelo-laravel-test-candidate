<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectCollection extends ResourceCollection
{
    public $collects = ProjectResource::class;

    public function toArray($request)
    {

        return $this->collection;
    }

    public function with($request)
    {
        return [
            'status' => 'success',
            'count' => $this->collection->count(),
            'message' => $this->collection->isEmpty() ? 'No projects found' : null
        ];
    }
}
