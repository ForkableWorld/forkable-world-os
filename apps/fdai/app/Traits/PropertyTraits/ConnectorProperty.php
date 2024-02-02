<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\Connector;
use App\Traits\HardCodableProperty;
use App\Traits\HasModel\HasConnector;
trait ConnectorProperty {
	use HasConnector, HardCodableProperty;
	public function getConnectorId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getConnector(): Connector{
		return $this->getParentModel();
	}
}
