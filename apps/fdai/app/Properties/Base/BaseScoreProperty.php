<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseScoreProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsInt;
	public $canBeChangedToNull = true;
	public $dbInput = 'integer,false';
	public $dbType = self::TYPE_INTEGER;
	public $default = 'undefined';
	public $description = 'score';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::JS_UNDERSCORE;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'score';
	public $order = 99;
	public $phpType = \App\Types\PhpTypes::DOUBLE;
	public $showOnDetail = true;
	public $title = 'Score';
	public $type = \App\Types\PhpTypes::DOUBLE;

}
