<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasProperty;
use App\Properties\Base\BaseCreatedAtProperty;
use App\Properties\Base\BaseDeletedAtProperty;
use App\Properties\Base\BaseUpdatedAtProperty;
use App\Properties\BaseProperty;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;

trait HasCreatedUpdatedDeletedAts {
    use HasTimestamps;
	/**
	 * @return BaseCreatedAtProperty|BaseProperty
	 */
	public function getCreatedAtProperty(): BaseCreatedAtProperty{
		return $this->getPropertyModel(BaseCreatedAtProperty::NAME);
	}
	/**
	 * @return BaseUpdatedAtProperty|BaseProperty
	 */
	public function getUpdatedAtProperty(): BaseUpdatedAtProperty{
		return $this->getPropertyModel(BaseUpdatedAtProperty::NAME);
	}
	/**
	 * @return BaseDeletedAtProperty|BaseProperty
	 */
	public function getDeletedAtProperty(): BaseDeletedAtProperty{
		return $this->getPropertyModel(BaseDeletedAtProperty::NAME);
	}
}
