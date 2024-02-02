<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Properties\BaseProperty;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class BaseUserStudyTextProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'text:nullable';
	public $dbType = 'text';
	public $default = Generator::UNDEFINED;
	public $description = 'Overrides auto-generated study text';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::CREATE_STUDY;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::STUDY;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'user_study_text';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable';
	public $title = 'User Study Text';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable';
	/**
	 * @param string $studyText
	 * @return mixed|string
	 */
	public static function humanizeStudyText(string $studyText): string{
		$replacements = [
			'Higher Sleep Start Time' => 'Later Sleep Start Time',
			'Lower Sleep Start Time' => 'Earlier Sleep Start Time',
			'Higher Ate Lunch' => 'Eating Lunch',
			//'Blood Pressure (Systolic - Top Number)' => 'Blood Pressure',  We should be using display name from variable anyway and this might have unintended consequences
			//'Blood Pressure (Diastolic - Bottom Number)' => 'Blood Pressure',
		];
		foreach($replacements as $key => $value){
			$studyText = str_replace($key, $value, $studyText);
		}
		return $studyText;
	}
}
