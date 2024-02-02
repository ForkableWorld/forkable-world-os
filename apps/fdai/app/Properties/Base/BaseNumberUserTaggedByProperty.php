<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\UserTag;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNumberUserTaggedByProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'number_user_tagged_by';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::PHONE_NUMBER;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::PHONE_NUMBER;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'number_user_tagged_by';
	public $canBeChangedToNull = true;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $showOnDetail = true;
	public $title = 'Number User Tagged By';
	public $type = self::TYPE_INTEGER;
    protected static function getRelationshipClass():string{return UserTag::class;}
}
