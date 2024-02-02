<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Logging\QMLog;
use App\Properties\Base\BaseNameProperty;
use Illuminate\Support\Collection;
trait HasName {
	public static function getNameAttributeName(): string{
		return BaseNameProperty::NAME;
	}
	abstract public function getNameAttribute(): string;
	/**
	 * @param \ArrayAccess $arr
	 * @return static[]
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function indexByName($arr): array{
		$byName = [];
		foreach($arr as $item){
			$byName[$item->getName()] = $item;
		}
		return $byName;
	}
	/**
	 * @param \ArrayAccess $arr
	 * @return string[]
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public static function pluckNames($arr): array{
		return array_keys(static::indexByName($arr));
	}
	/**
	 * @param $variables
	 */
	public static function logNames($variables){
		foreach(static::pluckNames($variables) as $name){
			QMLog::infoFast($name);
		}
	}
	public static function getWhereNameLike(string $name): Collection{
		return static::query()->whereLike(static::getNameAttributeName(), '%' . $name . '%')
            ->get();
	}
	/**
	 * @param string $name
	 * @param Collection|array $arr
	 * @return Collection
	 */
	public static function filterWhereNameLike(string $name, $arr): Collection{
		return BaseNameProperty::filterWhereLike($name, $arr);
	}
	/**
	 * @param string $name
	 * @param Collection|array $arr
	 * @return Collection
	 */
	public static function filterWhereNameStartsWith(string $name, $arr): Collection{
		return BaseNameProperty::filterWhereStartsWith($name, $arr);
	}
}
