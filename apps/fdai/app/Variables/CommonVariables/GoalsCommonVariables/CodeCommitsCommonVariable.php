<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\GoalsCommonVariables;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\Base\BaseValenceProperty;
use App\Variables\QMCommonVariable;
use App\VariableCategories\GoalsVariableCategory;
use App\Units\EventUnit;
class CodeCommitsCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = EventUnit::ID;
	public const DEFAULT_VALUE = 1.0;
	public const DURATION_OF_ACTION = 604800;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_ZERO;
	public const ID = 5955693;
	public const ION_ICON = 'ion-ios-body-outline';
    public const MANUAL_TRACKING = false;
	public const NAME = 'Code Commits';
	public const PUBLIC = true;
	public const SYNONYMS = ['Code Commits', 'Code Commit'];
	public const VARIABLE_CATEGORY_ID = GoalsVariableCategory::ID;
    public const VALENCE = BaseValenceProperty::VALENCE_POSITIVE;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $defaultValue = self::DEFAULT_VALUE;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $fillingType = self::FILLING_TYPE;
	public $id = self::ID;
	public $ionIcon = self::ION_ICON;
    public $manualTracking = self::MANUAL_TRACKING;
	public $name = self::NAME;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
    public $valence = self::VALENCE;
}
