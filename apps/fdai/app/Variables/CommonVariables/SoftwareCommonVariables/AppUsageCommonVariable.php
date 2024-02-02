<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\SoftwareCommonVariables;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Variables\QMCommonVariable;
use App\VariableCategories\SoftwareVariableCategory;
use App\Units\SecondsUnit;
class AppUsageCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = SecondsUnit::ID;
	public const DURATION_OF_ACTION = 604800;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_ZERO;
	public const ID = 1265;
	public const IMAGE_URL = SoftwareVariableCategory::IMAGE_URL;
	public const NAME = 'App Usage';
	public const PUBLIC = true;
	public const SYNONYMS = ['App Usage'];
	public const VARIABLE_CATEGORY_ID = SoftwareVariableCategory::ID;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $fillingType = self::FILLING_TYPE;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $name = self::NAME;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
