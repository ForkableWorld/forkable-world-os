<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\ConnectorImport;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasConnectorImport {
	public function getConnectorImportId(): int{
		$nameOrId = $this->getAttribute('connector_import_id');
		return $nameOrId;
	}
	public function getConnectorImportButton(): QMButton{
		$connectorImport = $this->getConnectorImport();
		if($connectorImport){
			return $connectorImport->getButton();
		}
		return ConnectorImport::generateDataLabShowButton($this->getConnectorImportId());
	}
	/**
	 * @return ConnectorImport
	 */
	public function getConnectorImport(): ConnectorImport{
		if($this instanceof BaseProperty && $this->parentModel instanceof ConnectorImport){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('connector_import')){
			return $l;
		}
		$id = $this->getConnectorImportId();
		$connectorImport = ConnectorImport::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['connector_import'] = $connectorImport;
		}
		if(property_exists($this, 'connectorImport')){
			$this->connectorImport = $connectorImport;
		}
		return $connectorImport;
	}
	public function getConnectorImportNameLink(): string{
		return $this->getConnectorImport()->getDataLabDisplayNameLink();
	}
	public function getConnectorImportImageNameLink(): string{
		return $this->getConnectorImport()->getDataLabImageNameLink();
	}
}
