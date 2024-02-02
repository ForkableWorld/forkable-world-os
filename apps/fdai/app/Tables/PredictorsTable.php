<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Models\UserVariableRelationship;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseEffectFollowUpPercentChangeFromBaselineProperty;
use App\Traits\HasOutcomesAndPredictors;
use App\Variables\QMVariable;
class PredictorsTable extends VariableRelationshipsTable {
	public $id = 'predictors-data-table-id';
	protected $orderColumnIndex = 0;
	protected $orderDirection = self::DESC;
	/**
	 * @var UserVariable|Variable|QMVariable
	 */
	private $variable;
	/**
	 * @param HasOutcomesAndPredictors $variable
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 */
	public function __construct($variable, int $limit = null, string $variableCategoryName = null){
		$this->variable = $variable;
		$predictors = $variable->getPredictors($limit, $variableCategoryName);
		parent::__construct($predictors);
		$this->addGauge();
		$this->addChangeLink();
		$this->addCauseName();
	}
	public function addChangeLink(){
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->column()->title('Change in ' . $this->getVariable()->getTitleAttribute())
			//->attr('th', 'title', $this->getSubtitleAttribute())
			->value(function($correlation){
				/** @var UserVariableRelationship $correlation */
				$change = $correlation->getChangeFromBaseline();
				$changeStr = BaseEffectFollowUpPercentChangeFromBaselineProperty::generateString($change, 2);
				$url = $correlation->getUrl();
				$color = $correlation->getColor();
				$effect = $correlation->getEffectVariableName();
				return "
<span style=\"font-size: 12px;\">$effect</span><br>
<a style=\"color: $color; font-size: 20px; text-align: center;\" href=\"$url\">
    $changeStr
</a>
";
			})->attr('td', 'data-order', function($correlation){
				/** @var UserVariableRelationship $correlation */
				return $correlation->getChangeFromBaseline();
			})->add();
	}
	/**
	 * @return UserVariable|Variable|QMVariable
	 */
	public function getVariable(){
		return $this->variable;
	}
	protected function getTitleAttribute(): string{
		return "Predictors from Longitudinal Studies";
	}
	protected function getSubtitleAttribute(): string{
		return "Possible factors influencing " . $this->getVariable()->getTitleAttribute() .
			" based on pharmacokinetic modeling predictive analysis of longitudinal self-tracking data. ";
	}
}
