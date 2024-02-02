<?php

namespace App\Http\Resources;


use Illuminate\Http\Request;

/** @mixin \App\Models\DeviceToken */
class DeviceTokenResource extends BaseJsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'device_token' => $this->device_token,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'number_of_waiting_tracking_reminder_notifications' => $this->number_of_waiting_tracking_reminder_notifications,
            'last_notified_at' => $this->last_notified_at,
            'platform' => $this->platform,
            'number_of_new_tracking_reminder_notifications' => $this->number_of_new_tracking_reminder_notifications,
            'number_of_notifications_last_sent' => $this->number_of_notifications_last_sent,
            'error_message' => $this->error_message,
            'last_checked_at' => $this->last_checked_at,
            'received_at' => $this->received_at,
            'server_ip' => $this->server_ip,
            'server_hostname' => $this->server_hostname,

            'user_id' => $this->user_id,
            'client_id' => $this->client_id,

            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
