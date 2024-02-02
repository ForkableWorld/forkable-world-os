<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\ConnectorImport;
use App\Traits\HasModel\HasConnectorImport;
trait ConnectorImportProperty {
	use HasConnectorImport;
	public function getConnectorImportId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getConnectorImport(): ConnectorImport{
		return $this->getParentModel();
	}
}
