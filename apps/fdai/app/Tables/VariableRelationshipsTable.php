<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Models\UserVariableRelationship;
use App\UI\FontAwesome;
class VariableRelationshipsTable extends BaseTable {
	public $id = 'user_variable_relationships-data-table-id';
	protected $orderColumnIndex = 0;
	protected $orderDirection = self::DESC;
	/**
	 * StrategyComparisonTable constructor.
	 * @param array $correlations
	 */
	public function __construct($correlations){
		parent::__construct();
		$this->setData($correlations);
	}
	public function addCauseName(){
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->column()->title('Predictor ' . FontAwesome::html(FontAwesome::QUESTION_CIRCLE))
			->attr('th', 'title', 'Predictor variable in the relationship. ')->value(function($c){
				/** @var UserVariableRelationship $c */
				$causeUrl = $c->getCauseUrl();
				$color = $c->getColor();
				$causeName = $c->getCauseVariableName();
				return "
<span style=\"font-size: 12px;\">following above average</span><br>
<a style=\"color: $color; font-size: 20px; text-align: center;\" href=\"$causeUrl\">
    $causeName
</a>
";
			})->attr('td', 'data-order', function($c){
				/** @var UserVariableRelationship $c */
				return $c->getCauseVariableName();
			})->add();
	}
	public function addChangeLink(){
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->column()->title('Change from Baseline')
			//->attr('th', 'title', $this->getSubtitleAttribute())
			->value(function($correlation){
				/** @var UserVariableRelationship $correlation */
				return $correlation->getGaugeImageHtml("max-height: 50px;");
			})->attr('td', 'data-order', function($correlation){
				/** @var UserVariableRelationship $correlation */
				return $correlation->getCauseVariableName() . " " . $correlation->getChangeFromBaseline();
			})->add();
	}
	public function addGauge(){
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->column()->title(' ')
			//->attr('th', 'title', $this->getSubtitleAttribute())
			->value(function($correlation){
				/** @var UserVariableRelationship $correlation */
				return $correlation->getGaugeImageHtml("max-height: 60px;");
			})->attr('td', 'data-order', function($correlation){
				/** @var UserVariableRelationship $correlation */
				return $correlation->getChangeFromBaseline();
			})->add();
	}
	protected function getTitleAttribute(): string{
		return "VariableRelationships";
	}
	protected function getSubtitleAttribute(): string{
		return "Relationships between predictor and outcome variables";
	}
}
