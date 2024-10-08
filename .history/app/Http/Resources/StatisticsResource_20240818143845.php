<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'sum' => $this->sum,
            'avg' => $this->avg,
            'max' => $this->max,
            'min' => $this->min,
            'count' => $this->count,
        ];
    }
}
