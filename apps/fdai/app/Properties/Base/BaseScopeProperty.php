<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Properties\BaseProperty;
use App\Slim\Configuration\RouteConfiguration;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class BaseScopeProperty extends BaseProperty{
	use IsString;

    const WRITE_SCOPE = 'writemeasurements';
    const READ_SCOPE = 'readmeasurements';
    public $dbInput = 'string,2000:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'scope';
	public $example = 'basic readmeasurements writemeasurements';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::MICROSCOPE_SOLID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::DATA_SOURCES_MOODSCOPE_GXWRBU;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 2000;
	public $name = self::NAME;
	public const NAME = 'scope';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:2000';
	public $title = 'Scope';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:2000';
	/**
	 * @param [] $scopeArray
	 * @return string
	 */
	public static function scopeDescriptionsString(array $scopeArray): string{
		$descriptions = self::scopeDescriptionsArray($scopeArray);
		$descriptions = implode("\n<br>", $descriptions);
		return $descriptions;
	}
	/**
	 * @param string|null $scopeString
	 * @return string
	 */
	public static function getScopeDescriptionFromString(?string $scopeString): string{
		if(!$scopeString){
			return "No permission scopes have been defined";
		}
		$scopeArray = explode(" ", $scopeString);
		return self::scopeDescriptionsString($scopeArray);
	}
	/**
	 * @param array $scopeArray
	 * @return array
	 */
	public static function scopeDescriptionsArray(array $scopeArray): array{
		$descriptions = [];
		foreach($scopeArray as $item){
			if($item === 'basic'){
				$descriptions[] = 'Access your name and email.  ';
			}
			if($item === RouteConfiguration::SCOPE_READ_MEASUREMENTS){
				$descriptions[] = 'View your measurements';
			}
			if($item === 'writemeasurements'){
				$descriptions[] = 'Create or modify your measurements';
			}
			if(stripos($item, 'measurements:read:')){
				$variable = urldecode(str_replace('measurements:read:', '', $item));
				$descriptions[] = 'View your '.$variable.' measurements.  ';
			}
			if(stripos($item, 'measurements:write:')){
				$variable = urldecode(str_replace('measurements:write:', '', $item));
				$descriptions[] = 'Create or modify any of your '.$variable.' measurements.  ';
			}
		}
		return $descriptions;
	}
}
