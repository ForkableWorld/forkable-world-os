<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\WpPost;
use App\Traits\HasModel\HasWpPost;
trait WpPostProperty {
	use HasWpPost;
	public function getWpPostId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getWpPost(): WpPost{
		return $this->getParentModel();
	}
}
