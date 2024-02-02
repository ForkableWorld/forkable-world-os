<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\IsImageUrl;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use OpenApi\Generator;
class BaseImageUrlProperty extends BaseProperty{
    use IsImageUrl;
	public $dbInput = 'string,2083:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Image URL';
	public $example = 'https://web.quantimo.do/img/variable_categories/books-96.png';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::AVATAR_IMAGE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::AVATAR_IMAGE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $isImageUrl = true;
	public $isUrl = true;
	public $maxLength = 2083;
	public $name = self::NAME;
	public const NAME = 'image_url';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:2083';
	public $title = 'Image Url';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:2083';
	/**
	 * @return string|null
	 */
	public function getAccessorValue(): ?string{
        return parent::getDBValue();
    }
}
