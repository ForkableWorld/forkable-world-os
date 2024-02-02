<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\EmotionsCommonVariables;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\EmotionsVariableCategory;
use App\Units\OneToFiveRatingUnit;
class OverallMoodCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = OneToFiveRatingUnit::ID;
	public const DEFAULT_VALUE = 3.0;
	public const DESCRIPTION = 'Your mood is the way you are feeling at a particular time. If you are in a good mood, you feel cheerful. If you are in a bad mood, you feel angry and impatient. ... If someone is in a mood, the way they are behaving shows that they are feeling angry and impatient.';
	public const ID = 1398;
    public const IMAGE_URL = ImageUrls::EMOTICON_SET_HAPPY_1;
	public const MANUAL_TRACKING = true;
	public const NAME = 'Overall Mood';
	public const PUBLIC = true;
	public const SYNONYMS = ['Mood', 'Overall Mood', 'Happy', 'Happiness'];
	public const VALENCE = 'positive';
	public const VARIABLE_CATEGORY_ID = EmotionsVariableCategory::ID;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $defaultValue = self::DEFAULT_VALUE;
	public $description = self::DESCRIPTION;
	public $id = self::ID;
    public $imageUrl = self::IMAGE_URL;
	public $manualTracking = self::MANUAL_TRACKING;
	public $name = self::NAME;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $valence = self::VALENCE;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;

}
