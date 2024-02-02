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
use Doctrine\DBAL\Types\Types;
use OpenApi\Generator;
class BaseMeasurementsAtLastAnalysisProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'integer,false,true';
	public $dbType = Types::INTEGER;
	public $default = Generator::UNDEFINED;
	public $description = 'Number of measurements at last analysis';
	public $example = 48;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MEASUREMENTS;
	public $htmlType = 'text';
	public $image = ImageUrls::MEASUREMENTS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 300000;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'measurements_at_last_analysis';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'integer|min:0|max:300000';
	public $title = 'Measurements Last Analysis';
	public $type = self::TYPE_INTEGER;
	public $validations = '';

}
