<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\ConnectorRequest;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasConnectorRequest {
	public function getConnectorRequestId(): int{
		$nameOrId = $this->getAttribute('connector_request_id');
		return $nameOrId;
	}
	public function getConnectorRequestButton(): QMButton{
		$connectorRequest = $this->getConnectorRequest();
		if($connectorRequest){
			return $connectorRequest->getButton();
		}
		return ConnectorRequest::generateShowButton($this->getConnectorRequestId());
	}
	/**
	 * @return ConnectorRequest
	 */
	public function getConnectorRequest(): ConnectorRequest{
		if($this instanceof BaseProperty && $this->parentModel instanceof ConnectorRequest){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('connector_request')){
			return $l;
		}
		$id = $this->getConnectorRequestId();
		$connectorRequest = ConnectorRequest::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['connector_request'] = $connectorRequest;
		}
		if(property_exists($this, 'connectorRequest')){
			$this->connectorRequest = $connectorRequest;
		}
		return $connectorRequest;
	}
}
