<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseFailingProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsBoolean;
	public $canBeChangedToNull = true;
	public $dbInput = 'boolean:nullable';
	public $dbType = self::TYPE_BOOLEAN;
	public $default = 'undefined';
	public $description = 'failing';
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'failing';
	public $order = 99;
	public $phpType = self::TYPE_BOOLEAN;
	public $showOnDetail = true;
	public $title = 'Failing';
	public $type = self::TYPE_BOOLEAN;

}
