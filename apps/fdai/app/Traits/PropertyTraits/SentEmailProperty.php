<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\SentEmail;
use App\Traits\HasModel\HasSentEmail;
trait SentEmailProperty {
	use HasSentEmail;
	public function getSentEmailId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getSentEmail(): SentEmail{
		return $this->getParentModel();
	}
}
