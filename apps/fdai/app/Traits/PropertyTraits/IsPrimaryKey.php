<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Fields\DateTime;
use App\Fields\Field;
trait IsPrimaryKey {
	public function showOnIndex(): bool{ return true; }
	public function showOnDetail(): bool{ return true; }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return DateTime
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return $this->getIdField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return $this->getIdField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->getIdField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return $this->getIdField($name, $resolveCallback);
	}
}
