<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use OpenApi\Generator;
class BaseSourceNameProperty extends BaseNameProperty{
	const MIN_LENGTH = 2;
	public $minLength = self::MIN_LENGTH;
	public $dbInput = 'string,80:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'Name of the application or device';
	public $example = 'quantimodo';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::SOURCE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::SOURCE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = 80;
	public $name = self::NAME;
	public const NAME = 'source_name';
	public $canBeChangedToNull = false;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:80';
	public $title = 'Source Name';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:80';
	public const SYNONYMS = [
	    'source_name',
        'source',
        'app_name',
        'client_id',
    ];
	public function showOnCreate(): bool{return false;}
	public function validate(): void{
		parent::validate();
	}
}
