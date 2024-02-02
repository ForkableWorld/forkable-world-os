<?php

namespace App\Http\Resources;


use Illuminate\Http\Request;

/** @mixin \App\Models\Collaborator */
class CollaboratorResource extends BaseJsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'app_id' => $this->app_id,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'user_id' => $this->user_id,
            'client_id' => $this->client_id,

            'application' => new ApplicationResource($this->whenLoaded('application')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
