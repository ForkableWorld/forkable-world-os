<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Fields\DateTime;
use App\Fields\Field;
trait IsDateTime {
	use IsTemporal;
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return DateTime
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return $this->getSimpleDateTimeField($resolveCallback, $name);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return DateTime
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return $this->getDateTimeField($resolveCallback, $name ?? $this->getTitleAttribute());
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return DateTime
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->getDateTimeField($resolveCallback, $name ?? $this->getTitleAttribute());
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return DateTime
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return $this->getDateTimeField($resolveCallback, $name ?? $this->getTitleAttribute());
	}
	public function showOnIndex(): bool{ return false; }
	public function showOnUpdate(): bool{ return false; }
	public function showOnCreate(): bool{ return false; }
	public function showOnDetail(): bool{ return true; }
    public function getMaxLength(): int{ return 20; }
}
