<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseReasonForAnalysisProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'reason_for_analysis';
	public $example = 'STUCK: analysis_started_at more than a day ago and never ended';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::ACTIVITY;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::AGGREGATE_CORRELATION;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'reason_for_analysis';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $title = 'Reason for Analysis';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:255';

}
