<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use OpenApi\Generator;
class BaseAmazonProductCategoryProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,100';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'The Amazon equivalent product category.';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::UNIT_CATEGORY;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::UNIT_CATEGORY;
	public $importance = false;
	public $required = false;
	public $canBeChangedToNull = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'amazon_product_category';
    public $phpType = PhpTypes::STRING;
	public $showOnDetail = true;
	public $title = 'Amazon Product Category';
	public $type = PhpTypes::STRING;
	public function isRequired(): bool{
        return parent::isRequired();
    }
    public function validate(): void {
        parent::validate();
    }
}
