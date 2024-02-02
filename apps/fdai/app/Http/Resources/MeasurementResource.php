<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Resources;

use App\Models\Connector;
use Illuminate\Http\Request;

/** @mixin \App\Models\Measurement */
class MeasurementResource extends BaseJsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $data = [
            'title' => $this->getTitleAttribute(),
            'subtitle' => $this->getSubtitleAttribute(),
            'id' => $this->id,
            'value' => $this->value,
            'start_at' => $this->start_at,
            'unit_name' => $this->getUnit()->name,
            'variable_name' => $this->getVariable()->name,
            'original_value' => $this->original_value,
            'client_id' => $this->client_id,
            'connection_id' => $this->connection_id,
            'connector_id' => $this->connector_id,
            'connector_import_id' => $this->connector_import_id,
            'created_at' => $this->created_at,
            'duration' => $this->duration,
            'error' => $this->error,
            'note' => $this->note,
            'original_start_at' => db_date($this->original_start_at),
            'original_unit_id' => $this->original_unit_id,
            'unit_id' => $this->unit_id,
            'updated_at' => $this->updated_at,
            'user_id' => $this->user_id,
            'user_variable_id' => $this->user_variable_id,
            'variable_category_id' => $this->variable_category_id,
            'variable_category_name' => $this->getVariableCategory()->name,
            'variable_id' => $this->variable_id,
            //'start_time' => $this->start_time,
            //'latitude' => $this->latitude,
            //'longitude' => $this->longitude,
            //'location' => $this->location,
            //'deletion_reason' => $this->deletion_reason,
            //'display_name' => $this->display_name,
            //'invalid_record_for' => $this->invalid_record_for,
            //'name' => $this->name,
            //'raw' => $this->raw,
            //'raw_variable' => $this->raw_variable,
            //'report_title' => $this->report_title,
            //'rule_for' => $this->rule_for,
            //'rules_for' => $this->rules_for,
            //'source_name' => $this->source_name,
            //'user' => new UserResource($this->whenLoaded('user')),
            //'user' => new UserResource($this->whenLoaded('user')),
            //'variable' => new VariableResource($this->whenLoaded('variable')),
            //'variable' => new VariableResource($this->whenLoaded('variable')),
        ];
        if($this->connector_id) {
            $data['connector_name'] = $this->getConnector()->name;
        }
        return $data;
    }
}
