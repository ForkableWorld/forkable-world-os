<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\Purchase;
use App\Traits\HasModel\HasPurchase;
trait PurchaseProperty {
	use HasPurchase;
	public function getPurchaseId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getPurchase(): Purchase{
		return $this->getParentModel();
	}
}
