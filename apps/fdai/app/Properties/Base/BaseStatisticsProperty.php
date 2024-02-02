<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Storage\DB\QMDB;
use App\Traits\PropertyTraits\IsJsonEncoded;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\VariableRelationships\QMVariableRelationship;
class BaseStatisticsProperty extends BaseProperty{
    use IsJsonEncoded;
	public $dbInput = 'text,65535:nullable';
	public $dbType = QMDB::TYPE_TEXT;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'statistics';
	public $example = '{\"aggregateQMScore\":0.008689258148640945,\"qmScore\":0.008689258148640945,\"numberOfCorrelations\":4,\"dataSourceName\":\"user\",\"predictorExplanationSentence\":\"Higher Curcumin 95 By Jarrow intake predicts very slightly lower Overall Mood. \",\"status\":\"ANALYZING\",\"averageDailyHighCause\":4240.708536585365,\"averageDailyLowCause\":335.3285579375743,\"averageEffect\":3.0963137909288023,\"averageEffectFollowingHighCause\":3.0101246348502375,\"averageEffectFollowingLowCause\":3.1033283257301663,\"causeChanges\":\"410\",\"causeVariableCategoryId\":13,\"causeVariableCategoryName\":\"Treatments\",\"causeVariableCommonUnitAbbreviatedName\":\"mg\",\"causeVariableCommonUnitId\":7,\"causeVariableCommonUnitName\":\"Milligrams\",\"causeVariableDisplayName\":\"Curcumin 95 By Jarrow intake\",\"causeVariableId\":5926104,\"causeVariableName\":\"Curcumin 95 By Jarrow\",\"confidenceInterval\":0.1460716133728158,\"confidenceLevel\":\"low\",\"correlationCoefficient\":-0.04185349658258163,\"createdAt\":\"2019-10-24 13:42:56\",\"criticalTValue\":1.6555,\"avgDailyValuePredictingHighOutcome\":\"1\",\"avgDailyValuePredictingLowOutcome\":1189.1686457191565,\"direction\":\"lower\",\"durationOfAction\":1814400,\"durationOfActionInHours\":0,\"effectChanges\":\"1151\",\"effectSize\":\"very weakly negative\",\"effectVariableCategoryId\":1,\"effectVariableCategoryName\":\"Emotions\",\"effectVariableCommonUnitAbbreviatedName\":\"\\/5\",\"effectVariableCommonUnitId\":10,\"effectVariableCommonUnitName\":\"1 to 5 Rating\",\"effectVariableDisplayName\":\"Overall Mood\",\"effectVariableId\":1398,\"effectVariableName\":\"Overall Mood\",\"effectVariableValence\":\"positive\",\"forwardPearsonCorrelationCoefficient\":-0.04185349658258163,\"groupedCauseValueClosestToValuePredictingHighOutcome\":375,\"groupedCauseValueClosestToValuePredictingLowOutcome\":500,\"numberOfPairs\":558,\"numberOfUsers\":4,\"onsetDelay\":1800,\"onsetDelayInHours\":0,\"optimalPearsonProduct\":0.001173041289223933,\"predictivePearsonCorrelationCoefficient\":-0.02727247995402824,\"predictorExplanation\":\"Higher Curcumin 95 By Jarrow Intake Predicts Very Slightly Lower Overall Mood\",\"predictsHighEffectChange\":0.5,\"predictsLowEffectChange\":-2.5,\"pValue\":0.1294620551810852,\"statisticalSignificance\":0.30647500144550577,\"strengthLevel\":\"very weak\",\"tValue\":1.5781582625273605,\"type\":\"population\",\"updatedAt\":\"2019-10-24 13:42:56\",\"id\":65683827}';
	public $fieldType = QMDB::TYPE_TEXT;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::DESIGN_TOOL_COLLECTION_STATISTICS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'statistics';
	public $canBeChangedToNull = true;
	public $phpType = QMVariableRelationship::class;
	public $title = 'Statistics';
	public $type = PhpTypes::OBJECT;
    public function getExample(){
        return json_decode($this->example);
    }
}
