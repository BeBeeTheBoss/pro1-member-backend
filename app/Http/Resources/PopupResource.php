<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PopupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $data = parent::toArray($request);

        $data['image'] = $data['image'] ? url("storage/popups/".$data['image']) : null;

        return $data;
    }
}
