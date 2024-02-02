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
class BaseVoteProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,3,1';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'vote';
	public $example = 0.5;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::VOTE;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::VOTE;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'vote';
	public $canBeChangedToNull = true;
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $showOnDetail = true;
	public $title = 'Vote';
	public $type = self::TYPE_NUMBER;
	public $validations = 'nullable|numeric';

}
