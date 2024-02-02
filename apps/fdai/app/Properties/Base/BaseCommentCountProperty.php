<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCommentCountProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'bigInteger,false';
	public $dbType = 'bigint';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Total number of comments, pingbacks and trackbacks.';
	public $fieldType = 'bigInteger';
	public $fontAwesome = FontAwesome::COMMENT_SOLID;
	public $htmlType = 'text';
	public $image = ImageUrls::COMMENT;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'comment_count';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|numeric';
	public $title = 'Comment Count';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';

}
