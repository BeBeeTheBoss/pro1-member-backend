<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GamesEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $data['image'] = $data['image'] ? url('storage/games-events/' . $data['image']) : null;
        $data['branches'] = $this->whenLoaded('branches');

        return $data;
    }
}
