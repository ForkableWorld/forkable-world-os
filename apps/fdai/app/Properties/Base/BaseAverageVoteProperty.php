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
class BaseAverageVoteProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,3,1';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The average of all user votes on this variable relationship. 1 means everyone agrees in the validity and usefulness of this relationship. 0 means everyone disagrees.';
	public $example = 0.5;
	public $minimum = 0;
	public $maximum = 1;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::CORRELATION_CAUSALITY_VOTE;
	public $htmlType = 'text';
	public $image = ImageUrls::CORRELATION_CAUSALITY_VOTE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'average_vote';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Average Vote';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';

}
