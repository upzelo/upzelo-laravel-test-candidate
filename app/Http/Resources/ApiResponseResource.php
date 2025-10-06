<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'data' => $this['data'] ? $this['data'] : [],
            'status' => $this['status'] ? $this['status'] : 'success',
            'message' => $this['message'] ? $this['message'] : null,
        ];
    }
}
