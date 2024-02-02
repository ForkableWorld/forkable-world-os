<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\Connection;
use App\Traits\HasModel\HasConnection;
trait ConnectionProperty {
	use HasConnection;
	public function getConnectionId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getConnection(): Connection{
		return $this->getParentModel();
	}
}
