<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Fields\Field;
use Spatie\TagsField\Tags;
trait IsTagList {
	use IsJsonEncoded;
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field|null
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function getField($resolveCallback = null, string $name = null): ?Field{
		return Tags::make($name ?? $this->getTitleAttribute());
	}
	public function showOnUpdate(): bool{ return true; }
	public function showOnCreate(): bool{ return false; }
}
