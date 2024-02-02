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
class BaseIonIconProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,40:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'ion_icon';
	public $example = 'ion-laptop';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::ACCESSIBLE_ICON;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CROWD_SOURCING_UTOPIA_BRAIN_ICON;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 40;
	public $name = self::NAME;
	public const NAME = 'ion_icon';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:40';
	public $title = 'Ion Icon';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:40';

}
