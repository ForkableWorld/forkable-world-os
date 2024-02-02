<?php

namespace App\Http\Resources;


use App\DataSources\QMDataSource;
use App\Types\QMArr;
use Auth;
use Illuminate\Http\Request;

/** @mixin \App\Models\Connector */
class ConnectorResource extends BaseJsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request) :array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'image' => $this->image,
            'get_it_url' => $this->get_it_url,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'enabled' => $this->enabled,
            'oauth' => $this->oauth,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'qm_client' => $this->qm_client,
            'connections_count' => $this->connections_count,
            'measurements_count' => $this->measurements_count,
            'connector_imports_count' => $this->connector_imports_count,
            'number_of_connections' => $this->number_of_connections,
            'number_of_connector_imports' => $this->number_of_connector_imports,
            'number_of_connector_requests' => $this->number_of_connector_requests,
            'connector_requests_count' => $this->connector_requests_count,
            'number_of_measurements' => $this->number_of_measurements,
            'is_public' => $this->is_public,
            'sort_order' => $this->sort_order,
            'slug' => $this->slug,
            'available_outside_us' => $this->available_outside_us,
            'client_id' => $this->client_id,
            'wp_post_id' => $this->wp_post_id,
            //'measurements' => MeasurementResource::collection($this->whenLoaded('measurements')),
        ];
        if($u = Auth::user()){
            $connection = $u->getConnections()->where('connector_id', $this->id)->first();
            if($connection){
                $data['connection'] = $connection;
            }
        }
        $data = ConnectorResource::anonymousConnectorFormat($data);
        $data = $this->resource->removeDeprecatedAttributesFromArray($data);
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public static function anonymousConnectorFormat(array $data): array{
        $data = static::removeCountsUnlessRequested($data);
        $data = BaseJsonResource::removeDateAttributes($data);
        $qmConnector = QMDataSource::find($data['id']);
        $data['buttons'] = QMArr::toArray($qmConnector->getButtons());
        if(method_exists($qmConnector, 'getConnectInstructions')){
            $data['connect_instructions'] = QMArr::toArray($qmConnector->getConnectInstructions());
        }
        return $data;
    }
}
