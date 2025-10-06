<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskCollection extends ResourceCollection
{
    public $collects = TaskResource::class;

    public function toArray($request)
    {

        return $this->collection;
    }

    public function with($request)
    {
        return [
            'status' => 'success',
            'count' => $this->collection->count(),
            'message' => $this->collection->isEmpty() ? 'No Tasks found' : null
        ];
    }
}
