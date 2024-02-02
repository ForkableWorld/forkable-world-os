<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\Connector;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasConnector {
	public function getConnectorId(): int{
		$nameOrId = $this->getAttribute('connector_id');
		return $nameOrId;
	}
	public function getConnectorButton(): QMButton{
		$connector = $this->getConnector();
		if($connector){
			return $connector->getButton();
		}
		return Connector::generateDataLabShowButton($this->getConnectorId());
	}
	/**
	 * @return Connector
	 */
	public function getConnector(): Connector{
		if($this instanceof BaseProperty && $this->parentModel instanceof Connector){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('connector')){
			return $l;
		}
		$id = $this->getConnectorId();
		$connector = Connector::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['connector'] = $connector;
		}
		if(property_exists($this, 'connector')){
			$this->connector = $connector;
		}
		return $connector;
	}
	public function getConnectorNameLink(): string{
		return $this->getConnector()->getDataLabDisplayNameLink();
	}
	public function getConnectorImageNameLink(): string{
		return $this->getConnector()->getDataLabImageNameLink();
	}
}
