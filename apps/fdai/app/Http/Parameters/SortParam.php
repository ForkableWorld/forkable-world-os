<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Parameters;
use App\Models\BaseModel;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Slim\View\Request\QMRequest;
class SortParam extends BaseParam {
    const NAME_SORT = 'sort';

    public static function getSynonyms(): array{
		return [self::NAME_SORT];
	}
	/**
	 * @return string
	 */
	public static function getSortDirection(): ?string{
		$sort = self::getSort();
		if(!$sort){
			return null;
		}
		if(strpos($sort, '-') === 0){
			return BaseModel::ORDER_DIRECTION_DESC;
		}
		return BaseModel::ORDER_DIRECTION_ASC;
	}
	/**
	 * @param string $rawSort
	 * @return string
	 */
	public static function rawSortToOrder(string $rawSort): string{
		$order = (strpos($rawSort, '-') !== false) ? 'DESC' : 'ASC';
		return $order;
	}
	public static function getOrder(): ?string{
		return self::getSortDirection();
	}
	/**
	 * @return string
	 */
	public static function getSortWithoutDirection(): ?string{
		return QMRequest::getSortColumnName();
	}
	/**
	 * @return string
	 */
	public static function getSort(): ?string{
		$value = $_GET[self::NAME_SORT] ?? null;
		$value = GetUserVariableRequest::replaceLegacySort($value);
		return $value;
	}
}
