<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/** @mixin \App\Models\Vote */
class VoteResource extends BaseJsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'cause_variable_id' => $this->cause_variable_id,
            'effect_variable_id' => $this->effect_variable_id,
            'value' => $this->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'global_variable_relationship_id' => $this->global_variable_relationship_id,
            'is_public' => $this->is_public,

            'client_id' => $this->client_id,
            'user_id' => $this->user_id,
            'correlation_id' => $this->correlation_id,

            'user' => new UserResource($this->whenLoaded('user')),
            'cause_variable' => new VariableResource($this->whenLoaded('cause_variable')),
            'effect_variable' => new VariableResource($this->whenLoaded('effect_variable')),
            'global_variable_relationship' => new GlobalVariableRelationshipResource($this->whenLoaded('global_variable_relationship')),
            'correlation' => new CorrelationResource($this->whenLoaded('correlation')),
        ];
    }
}
