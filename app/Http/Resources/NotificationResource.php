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

        if ($data['recipient'] === 'specific') {
            $userId = UserNotification::where('notification_id', $data['id'])->value('user_id');
            $data['user'] = $userId
                ? User::where('id', $userId)->value('name')
                : 'Processing...';
        }

        $data['image'] = $data['image'] ? url("storage/notifications/".$data['image']) : null;
        $data['recipient_file_url'] = $data['recipient_file'] ? route('notifications.recipient-file', $data['id']) : null;

        return $data;
    }
}
