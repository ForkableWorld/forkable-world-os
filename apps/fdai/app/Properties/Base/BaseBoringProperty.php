<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsBoolean;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseBoringProperty extends BaseProperty{
	use IsBoolean;
	public $dbInput = 'boolean:nullable';
	public $dbType = self::TYPE_BOOLEAN;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'If boring, the item should be hidden by default.';
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'boring';
    public $canBeChangedToNull = false;
	public $phpType = PhpTypes::BOOL;
	public $showOnDetail = true;
	public $title = 'Boring';
	public $type = self::TYPE_BOOLEAN;

}
