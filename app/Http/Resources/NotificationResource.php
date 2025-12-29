<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UserNotification;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        if($data['recipient'] === 'specific') {

            $data['user'] = User::where('id', UserNotification::where('notification_id', $data['id'])->first()->user_id)->first()->name;
        }

        $data['image'] = $data['image'] ? url("storage/notifications/".$data['image']) : null;

        return $data;
    }
}
