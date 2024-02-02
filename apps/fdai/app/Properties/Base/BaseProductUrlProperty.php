<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsUrl;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use OpenApi\Generator;
class BaseProductUrlProperty extends BaseProperty{
	use IsUrl;
	public $dbInput = 'string,2083:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Product URL';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::PRODUCT_HUNT;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::AGRICULTURE_PRODUCT_BAG;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 2083;
	public $name = self::NAME;
	public const NAME = 'product_url';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:2083';
	public $title = 'Product Url';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:2083';
	public $isUrl = true;
    public function validate(): void {
        $url = $this->getDBValue();
        if ($url === "0") {return;} // We set to 0 if we couldn't get one so we don't keep trying
        parent::validate();
    }
}
