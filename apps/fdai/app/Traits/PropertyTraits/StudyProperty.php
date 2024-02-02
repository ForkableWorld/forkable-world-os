<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\Study;
use App\Traits\HasModel\HasStudy;
trait StudyProperty {
	use HasStudy;
	public function getStudyId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getStudy(): Study{
		return $this->getParentModel();
	}
}
