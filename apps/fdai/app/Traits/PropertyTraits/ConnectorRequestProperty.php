<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\ConnectorRequest;
use App\Traits\HasModel\HasConnectorRequest;
trait ConnectorRequestProperty {
	use HasConnectorRequest;
	public function getConnectorRequestId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getConnectorRequest(): ConnectorRequest{
		return $this->getParentModel();
	}
}
