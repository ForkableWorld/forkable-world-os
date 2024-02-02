<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsDateTime;
use App\Types\MySQLTypes;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Models\GlobalVariableRelationship;
use OpenApi\Generator;
class BaseAnalysisStartedAtProperty extends BaseProperty{
	use IsDateTime;
	public $dbInput = 'datetime:nullable';
	public $dbType = MySQLTypes::TIMESTAMP;
	public $default = Generator::UNDEFINED;
	public $description = "When the analysis started";
	public $example = '2020-07-07 07:15:13';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::ANALYSIS;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::AGGREGATE_CORRELATION;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
    public const NAME = GlobalVariableRelationship::FIELD_ANALYSIS_STARTED_AT;
    public $name = self::NAME;
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|date';
	public $title = 'Analysis Started';
	public $type = self::TYPE_DATETIME;
	public $validations = 'nullable|date';
}
