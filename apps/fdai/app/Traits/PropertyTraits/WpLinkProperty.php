<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\WpLink;
use App\Traits\HasModel\HasWpLink;
trait WpLinkProperty {
	use HasWpLink;
	public function getWpLinkId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getWpLink(): WpLink{
		return $this->getParentModel();
	}
}
