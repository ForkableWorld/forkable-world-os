<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Properties\Base;
use App\Properties\BaseProperty;
use App\Slim\View\Request\QMRequest;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class BaseTimezoneProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'timezone';
	public $example = 'Africa/Abidjan';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::TIMEZONE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::TIMEZONE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'timezone';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $title = 'Timezone';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:255';
	public const SYNONYMS = [
	    'timezone',
        'time_zone',
        'tz'
    ];
	/**
	 * @return string|int|null
	 */
	public static function fromRequest(bool $throwException = false){
		return QMRequest::headers('X-Timezone') ??
		       QMRequest::getParam([
			                      'timezone',
			                      'tz',
			                      'timeZoneOffset'
		                      ]);
	}
}
