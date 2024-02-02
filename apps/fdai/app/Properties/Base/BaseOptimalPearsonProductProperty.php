<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsFloat;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseOptimalPearsonProductProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Optimal Pearson Product';
	public $example = 0.13671173147092;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::PRODUCT_HUNT;
	public $htmlType = 'text';
	public $image = ImageUrls::AGRICULTURE_PRODUCT_BAG;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'optimal_pearson_product';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Optimal Pearson Product';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';

}
