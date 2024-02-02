<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Parameters;
use App\Exceptions\BadRequestException;
use Closure;
class LimitParam extends BaseParam {
	public const DEFAULT_LIMIT = 100;
	public const MAX_LIMIT = 2000;
	public const NAME = 'limit';
	public static function getSynonyms(): array{
		return [self::NAME];
	}
	/**
	 * @param int|null $default
	 * @param int|null $maxLimit
	 * @return int
	 */
	public static function getLimit(int $default = self::DEFAULT_LIMIT, int $maxLimit = self::MAX_LIMIT): ?int{
		$value = self::get(false, $default);
		if($value > $maxLimit){
			throw new BadRequestException("Maximum limit is $maxLimit. ");
		}
		if(!is_numeric($value)){
			throw new BadRequestException("Limit must be numeric");
		}
		return $value;
	}
	/**
	 * @return Closure
	 */
	public static function validateLimit(): Closure{
		return function(){
			return static::getLimit(self::DEFAULT_LIMIT, self::MAX_LIMIT);
		};
	}
}
