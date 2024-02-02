<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseLastBranchProperty extends BaseProperty
{
    use IsString;
	const ORIGIN = 'origin/';
	public $canBeChangedToNull = true;
	public $dbInput = 'string,255:nullable';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'last_branch';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::CODE_BRANCH_SOLID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CARD_LAST_FOUR;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'last_branch';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Last Branch';
	public $type = 'string';
	public $shouldNotContain = [self::ORIGIN];
	/**
	 * @param $value
	 * @return string|
	 */
	public function toDBValue($value): string{
		$val = parent::toDBValue($value);
		return str_replace(self::ORIGIN, '', $val);
	}
}
