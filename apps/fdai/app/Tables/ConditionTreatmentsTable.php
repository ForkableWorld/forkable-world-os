<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Exceptions\NotEnoughDataException;
use App\Logging\QMLog;
use App\Models\CtConditionTreatment;
use App\Models\Variable;
use App\Types\QMStr;
use App\UI\FontAwesome;
abstract class ConditionTreatmentsTable extends VariableRelationsTable {
	/**
	 * @param Variable $variable
	 * @throws NotEnoughDataException
	 */
	public function __construct($variable){
		parent::__construct($variable);
		$this->addImprovementColumn(CtConditionTreatment::FIELD_MAJOR_IMPROVEMENT);
		$this->addImprovementColumn(CtConditionTreatment::FIELD_MODERATE_IMPROVEMENT);
		$this->addImprovementColumn(CtConditionTreatment::FIELD_MUCH_WORSE);
		$this->addImprovementColumn(CtConditionTreatment::FIELD_NO_EFFECT);
		$this->addImprovementColumn(CtConditionTreatment::FIELD_WORSE);
		$this->addPopularityColumn();
	}
	public function addPopularityColumn(){
		$this->column()->title('Responses ')->attr('th', 'title', "Number of people who said they use this treatment")
			->value(function($treatment_side_effect){
				/** @var CtConditionTreatment $treatment_side_effect */
				$change = $treatment_side_effect->popularity;
				return $change;
			})->attr('td', 'data-order', function($treatment_side_effect){
				/** @var CtConditionTreatment $treatment_side_effect */
				return $treatment_side_effect->popularity;
			})->add();
	}
	public function addEffectivenessColumn(){
		$this->column()->title('Average Reported Effectiveness ' . FontAwesome::html(FontAwesome::QUESTION_CIRCLE) .
				" ")->attr('th', 'title', "Average reported effectiveness on a 5-star scale")
			->value(function($treatment_side_effect){
				/** @var CtConditionTreatment $treatment_side_effect */
				$stars = round($treatment_side_effect->average_effect / 20);
				return $stars . "-stars";
			})->attr('td', 'data-order', function($treatment_side_effect){
				/** @var CtConditionTreatment $treatment_side_effect */
				return round($treatment_side_effect->average_effect / 20);
			})->add();
	}
	public function addImprovementColumn(string $key){
		$this->column()->title(QMStr::titleCaseSlow($key) . ' ' . FontAwesome::html(FontAwesome::QUESTION_CIRCLE) . " ")
			->attr('th', 'title', "Percent of people reporting " . str_replace("_", " ", $key))
			->value(function($treatment_side_effect) use ($key){
				/** @var CtConditionTreatment $treatment_side_effect */
				$responses = $treatment_side_effect->popularity;
				if(!$responses){
					QMLog::error("no responses", $treatment_side_effect);
					return 0;
				}
				return round($treatment_side_effect->getAttribute($key) / $responses * 100) . "%";
			})->attr('td', 'data-order', function($treatment_side_effect) use ($key){
				/** @var CtConditionTreatment $treatment_side_effect */
				$responses = $treatment_side_effect->popularity;
				if(!$responses){
					QMLog::error("no responses", $treatment_side_effect);
					return 0;
				}
				return round($treatment_side_effect->getAttribute($key) / $responses * 100);
			})->add();
	}
}
