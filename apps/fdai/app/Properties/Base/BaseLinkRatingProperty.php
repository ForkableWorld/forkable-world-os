<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Properties\Base;
use App\Properties\BaseProperty;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseLinkRatingProperty extends BaseProperty
{
    use IsInt;
	public $canBeChangedToNull = true;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = 'undefined';
	public $description = 'Add a rating between 0-10 for the link.';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::LINK;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::WORK_PRODUCTIVITY_RATING;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = -2147483648;
	public $name = self::NAME;
	public const NAME = 'link_rating';
    public $order = '99';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:-2147483648|max:2147483647';
	public $showOnDetail = true;
	public $title = 'Link Rating';
	public $type = self::TYPE_INTEGER;
	public $validations = 'nullable|integer|min:-2147483648|max:2147483647';

}
