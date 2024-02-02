<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Connection */
class ConnectionResource extends BaseJsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'connector_id' => $this->connector_id,
            'connect_status' => $this->connect_status,
            'connect_error' => $this->connect_error,
            'update_requested_at' => $this->update_requested_at,
            'update_status' => $this->update_status,
            'update_error' => $this->update_error,
            'last_successful_updated_at' => $this->last_successful_updated_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'total_measurements_in_last_update' => $this->total_measurements_in_last_update,
            'user_message' => $this->user_message,
            'latest_measurement_at' => $this->latest_measurement_at,
            'import_started_at' => $this->import_started_at,
            'import_ended_at' => $this->import_ended_at,
            'reason_for_import' => $this->reason_for_import,
            'user_error_message' => $this->user_error_message,
            //'internal_error_message' => $this->internal_error_message,
            'connector_imports_count' => $this->connector_imports_count,
            'measurements_count' => $this->measurements_count,
            'number_of_connector_imports' => $this->number_of_connector_imports,
            'number_of_connector_requests' => $this->number_of_connector_requests,
            //'credentials' => $this->credentials,
            'imported_data_from_at' => $this->imported_data_from_at,
            'imported_data_end_at' => $this->imported_data_end_at,
            'connector_requests_count' => $this->connector_requests_count,
            'number_of_measurements' => $this->number_of_measurements,
            'actions_count' => $this->actions_count,
            //'raw' => $this->raw,
            'is_public' => $this->is_public,
            'slug' => $this->slug,
            'meta' => $this->meta,
            //'credentials_count' => $this->credentials_count,

            'client_id' => $this->client_id,
            'user_id' => $this->user_id,
            //'wp_post_id' => $this->wp_post_id,

            'connector' => new ConnectorResource($this->whenLoaded('connector')),
            'user' => new UserResource($this->whenLoaded('user')),
            'measurements' => MeasurementResource::collection($this->whenLoaded('measurements')),
        ];
    }
}
