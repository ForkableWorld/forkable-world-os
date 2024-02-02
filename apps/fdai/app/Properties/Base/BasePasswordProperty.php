<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\User;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BasePasswordProperty extends BaseProperty{
	use IsString;

    public const TEST_USER_PASSWORD_18535 = 'testing123';
    public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'password';
	public $example = '$P$BBAT7.X/pipFIuLRnyKOQS.fSpRg/v0';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'text';
	public $htmlType = 'password';
	public $image = ImageUrls::ACTIVITIES_PASSWORD;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'password';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $showOnDetail = true;
	public $title = 'Password';
	public $type = PhpTypes::STRING;
	public const SYNONYMS = [
		'pwd',
		'pass',
		'password',
		User::FIELD_USER_PASS,
	];
}
