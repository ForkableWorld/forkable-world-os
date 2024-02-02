<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\States\VariableSettingsStateButton;
use App\Charts\BarChartButton;
use App\Charts\CorrelationCharts\CorrelationsNetworkGraphQMChart;
use App\Charts\CorrelationCharts\CorrelationsSankeyQMChart;
use App\Charts\SankeyQMChart;
use App\VariableRelationships\CorrelationsAndExplanationResponseBody;
use App\VariableRelationships\QMVariableRelationship;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Tables\OutcomesTable;
use App\Tables\PredictorsTable;
use App\UI\HtmlHelper;
use App\Utils\AppMode;
use App\Variables\QMVariableCategory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Jupitern\Table\Table;
use LogicException;
trait HasOutcomesAndPredictors {
	abstract public function predictors(): HasMany;
	abstract public function outcomes(): HasMany;
	public function getOutcomeChips(): ?string{
		$buttons = $this->getOutcomeButtons();
		if(!$buttons){
			return null;
		}
		return HtmlHelper::renderView(view('chip-search', [
			'heading' => "Outcomes",
			'placeholder' => "Search to filter by outcome name",
			'searchId' => "outcome-chips",
			'buttons' => $buttons,
		]));
	}
	public function getPredictorChips(): ?string{
		$buttons = $this->getPredictorButtons();
		if(!$buttons){
			return null;
		}
		return HtmlHelper::renderView(view('chip-search', [
			'heading' => "Predictors",
			'placeholder' => "Search to filter by predictor name",
			'searchId' => "predictor-chips",
			'buttons' => $buttons,
		]));
	}
	/**
	 * @return PredictorsTable
	 * @throws NotEnoughDataException
	 */
	public function getPredictorsTable(): PredictorsTable{
		return new PredictorsTable($this);
	}
	/**
	 * @return string
	 * @throws NotEnoughDataException
	 */
	public function getPredictorsTableHtml(): string{
		return $this->getPredictorsTable()->render(true);
	}
	/**
	 * @return string
	 */
	public function getPredictorsFullWidthBarChart(): ?string{
		$buttons = $this->getPredictorButtons();
		if(!$buttons){
			return null;
		}
		return BarChartButton::renderFullWidthBarChart("Predictors Effect Sizes", $buttons);
	}
	/**
	 * @return string
	 */
	public function getOutcomesFullWidthBarChart(): ?string{
		$buttons = $this->getOutcomeButtons();
		if(!$buttons){
			return null;
		}
		return BarChartButton::renderFullWidthBarChart("Outcome Effect Sizes", $buttons);
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return string
	 */
	public function getPredictorsImageBarChart(int $limit = null, string $variableCategoryName = null): ?string{
		$buttons = $this->getPredictorButtons($limit, $variableCategoryName);
		if(!$buttons){
			return null;
		}
		return BarChartButton::renderImagesBarChart("Predictors Effect Sizes", $buttons, "Filter by predictor name...");
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return string
	 */
	public function getOutcomesImageBarChart(int $limit = null, string $variableCategoryName = null): ?string{
		$buttons = $this->getOutcomeButtons($limit, $variableCategoryName);
		if(!$buttons){
			return null;
		}
		return BarChartButton::renderImagesBarChart("Outcome Effect Sizes", $buttons, "Filter by predictor name...");
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return string
	 */
	public function getPredictorsEmailBarChart(int $limit = null, string $variableCategoryName = null): ?string{
		$buttons = $this->getPredictorButtons($limit, $variableCategoryName);
		if(!$buttons){
			return null;
		}
		return HtmlHelper::renderView(view('bar-chart-with-images', ['buttons' => $buttons]));
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return string
	 */
	public function getOutcomesEmailBarChart(int $limit = null, string $variableCategoryName = null): ?string{
		$buttons = $this->getOutcomeButtons($limit, $variableCategoryName);
		if(!$buttons){
			return null;
		}
		return HtmlHelper::renderView(view('bar-chart-with-images', ['buttons' => $buttons]));
	}
	/**
	 * @return OutcomesTable
	 * @throws NotEnoughDataException
	 */
	public function getOutcomesTable(int $limit = null, string $variableCategoryName = null): OutcomesTable{
		return new OutcomesTable($this, $limit, $variableCategoryName);
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return string
	 * @throws NotEnoughDataException
	 * @noinspection PhpUnused
	 */
	public function getOutcomesTableHtml(int $limit = null, string $variableCategoryName = null): string{
		$t = $this->getOutcomesTable($limit, $variableCategoryName);
		return $t->render(true);
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return BarChartButton[]
	 */
	public function getPredictorButtons(int $limit = null, string $variableCategoryName = null): array{
		$predictors = $this->getPredictors($limit, $variableCategoryName);
		$buttons = [];
		$maxChange = HasCorrelationCoefficient::getMaxChange($predictors);
		foreach($predictors as $predictor){
			$b = $predictor->getBarChartButton($maxChange);
			$b->setTextAndTitle($predictor->getCauseVariableName());
			$b->setImage($predictor->getCauseVariableImage());
			$buttons[] = $b;
		}
		return $buttons;
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return BarChartButton[]
	 */
	public function getOutcomeButtons(int $limit = null, string $variableCategoryName = null): array{
		$outcomes = $this->getOutcomes($limit, $variableCategoryName);
		$buttons = [];
		$maxChange = HasCorrelationCoefficient::getMaxChange($outcomes);
		foreach($outcomes as $outcome){
			$b = $outcome->getBarChartButton($maxChange);
			$b->setTextAndTitle($outcome->getEffectVariableName());
			$b->setImage($outcome->getEffectVariableImage());
			$buttons[] = $b;
		}
		return $buttons;
	}
	/**
	 * @return string
	 */
	public function getNoCorrelationsDataRequirementAndCurrentDataQuantityHtml(): string{
		$name = $this->getDisplayNameAttribute();
		$html = "<p>We haven't found any relationships with $name, yet.</p>";
		$html .= NotEnoughMeasurementsForCorrelationException::DATA_REQUIREMENT_FOR_CORRELATIONS_HTML;
		$html .= $this->getDataQuantityHTML();
		return HtmlHelper::globalWrapper($html);
	}
	/** @noinspection PhpUnused */
	public function renderCorrelationsTable(int $limit = null, string $variableCategoryName = null): string{
		$tableId = 'data-table-id';
		$correlations = $this->getOutcomesOrPredictors($limit, $variableCategoryName);
		$me = $this;
		$html = Table::instance()->setData($correlations)->attr('table', 'id', $tableId)
				->attr('table', 'class', 'table table-bordered table-striped table-hover')
				->attr('table', 'cellspacing', '0')->attr('table', 'width', '100%')
				//->attr('table', 'style', 'width: 100%; table-layout: fixed;')
				->column()->filter()->title('Variable')->value(function($row) use ($me){
					/** @var QMVariableRelationship $row */
					if($row->causeVariableId === $me->id){
						$name = $row->effectNameWithSuffix();
						$id = $row->effectVariableId;
					} else{
						$name = $row->causeNameWithSuffix();
						$id = $row->causeVariableId;
					}
					/** @var QMVariableRelationship $row */
					$url = Variable::generateShowUrlById($id);
					return '<a href="' . $url . '"
                        style="cursor: pointer;">' . $name . '</a>';
				})->attr('td', 'style', 'width: 75%;')->css('td', 'width', '80%')->attr('td', 'width', '80%')->add()
				->column()->title('Effect')->value(function($row) use ($me){
					/** @var QMVariableRelationship $row */
					return $row->getEffectSizeLinkToStudyWithExplanation();
				})
				//->css('td', 'color', 'red')
				->css('td', 'width', '5%')->attr('td', 'width', '5%')->add()->column()->title('effectSize')
				->value('effectSize')
				//->css('td', 'color', 'red')
				->css('td', 'width', '5%')->attr('td', 'width', '5%')->add()
				//            ->column()
				//            ->title('causeVariableName')
				//            ->value('causeVariableName')
				//            ->css('td', 'width', '5%')
				//            ->attr('td', 'width', '5%')
				//            ->add()
				//            ->column()
				//            ->title('effectVariableName')
				//            ->value('effectVariableName')
				//            ->css('td', 'width', '5%')
				//            ->attr('td', 'width', '5%')
				//            ->add()
				// TODO: Maybe implement explain
				->column()
				//                ->value(function ($row) {
				//                    return '<a href="https://local.quantimo.do/sql/explain?sql='.
				//                        urlencode($row->sql_text).
				//                        '">Full Query</a>';
				//                })
				->value(function($row) use ($tableId){
					/** @var QMVariableRelationship $row */
					$url = $row->getInteractiveStudyUrl();
					return '<a href="' . $url . '"
                        style="cursor: pointer;">Full Study</a>';
				})->css('td', 'color', 'blue')->css('td', 'width', '5%')->attr('td', 'width', '5%')->add()
				->render(true) . "

                <script>
                    $(document).ready( function () {
                        $('#data-table-id').DataTable({
                            \"pageLength\": 50,
                            \"order\": [[ 0, \"desc\" ]] // Descending duration
                        });
                    } );
                </script>

            ";
		return $html;
	}
	public function isCause(): bool{
		return $this->isPredictor();
	}
	/**
	 * @return string
	 */
	public function getCorrelationDataRequirementAndCurrentDataQuantityString(): string{
		return NotEnoughMeasurementsForCorrelationException::DATA_REQUIREMENT_FOR_CORRELATIONS_STRING . "\n" .
			$this->getMeasurementQuantitySentence() . "\n";
	}
	public function getNodeChartSubTitle(int $limit = null, string $variableCategoryName = null): string{
		$correlations = $this->getOutcomesOrPredictors($limit, $variableCategoryName);
		if(!$correlations->count()){
			//            throw new NotEnoughDataException($variable, "Not Enough Data",
			//                "No Correlational Analyses Available to Create Chart");
			return $this->getCorrelationDataRequirementAndCurrentDataQuantityString();
		} elseif($this->isOutcome()){
			return "The percent value indicates the typical change in $this->name from baseline following above average measurements for the predictor on the left.";
		} else{
			return "The percent value indicates the typical change from baseline in the outcome on the right following above average measurements for $this->name.";
		}
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return string
	 */
	public function getCorrelationGaugesListHtml(int $limit = null, string $variableCategoryName = null): string{
		$name = $this->getDisplayNameAttribute();
		if($this->isOutcome()){
			$html = "<h4 class=\"text-2xl font-semibold\">Factors Most Likely to Impact $name</h4>";
		} else{
			$html = "<h4 class=\"text-2xl font-semibold\">Outcomes Most Likely to be Impacted by $name</h4>";
		}
		$correlations = $this->getOutcomesOrPredictors($limit, $variableCategoryName);
		if(!$correlations){
			$req = $this->getNoCorrelationsDataRequirementAndCurrentDataQuantityHtml();
			if(stripos($html, $req) === false){
				$html .= $req;
			}
			if(AppMode::isApiRequest()){
				$number = $this->getNumberOfVariablesToCorrelateWith();
				if($number){
					$html .= "<h6> Correlational Analysis has Been Requested</h6>
                        <p>You have data for $number variables that can be correlated with $name.</p>
                        <p> Analysis has been queued and you will be notified upon completion.</p>";
					$this->queue("Correlational analysis scheduled due to insufficient time to analyze during a web request. ");
				} else{
					$html .= "<h6> Not Enough Other Variables Data</h6>
                        <p>You have data for $number variables that can be correlated with $name.</p>";
				}
				$html .= HtmlHelper::getHelpButton();
			}
			$html = "
                <div id=\"analysis-requested-text\">
                    $html
                </div>
            ";
			return $html;
		}
		if($this->isOutcome()){
			$params['effectVariableName'] = $this->getDisplayNameAttribute();
		} else{
			$params['causeVariableName'] = $this->getDisplayNameAttribute();
		}
		$response = new CorrelationsAndExplanationResponseBody($correlations, $params);
		return $response->getHtml();
	}
	/**
	 * @return GlobalVariableRelationship[]|Collection
	 */
	public function getCorrelationsChartTitle(): string{
		if($this->isOutcome()){
			return $this->getDisplayNameAttribute() . " Predictors";
		} else{
			return $this->getDisplayNameAttribute() . " Outcomes";
		}
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return GlobalVariableRelationship[]|UserVariableRelationship[]|Collection
	 */
	public function getOutcomesOrPredictors(int $limit = null, string $variableCategoryName = null): ?Collection{
		if($this->isOutcome()){
			$correlations = $this->getPredictors($limit, $variableCategoryName);
		} else{
			$correlations = $this->getOutcomes($limit, $variableCategoryName);
		}
		return $correlations;
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return GlobalVariableRelationship[]|UserVariableRelationship[]|Collection
	 */
	public function getPublicOutcomesOrPredictors(int $limit = null, string $variableCategoryName = null): ?Collection{
		if($variableCategoryName){
			$variableCategory = VariableCategory::findByNameIdOrSynonym($variableCategoryName);
		} else{
			$variableCategory = null;
		}
		if($this->isOutcome()){
			$correlations = $this->getPublicPredictors();
			if($variableCategory){
				$correlations = $correlations->filter(function(UserVariableRelationship $correlation) use ($variableCategory){
					return $correlation->cause_variable_category_id === $variableCategory->getId();
				});
			}
		} else{
			$correlations = $this->getPublicOutcomes();
			if($variableCategory){
				$correlations = $correlations->filter(function(UserVariableRelationship $correlation) use ($variableCategory){
					return $correlation->effect_variable_category_id === $variableCategory->getId();
				});
			}
		}
		return $correlations->take($limit);
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return UserVariableRelationship[]|Collection
	 */
	public function getPredictors(int $limit = null, string $variableCategoryName = null): Collection{
		$noLimitKey = __FUNCTION__ . "-category-$variableCategoryName-limit-";
		/** @var Collection $correlations */
		if($correlations = $this->getFromModelMemory($noLimitKey)){
			return $correlations->take($limit);
		}
		$key = $noLimitKey . $limit;
		if($correlations = $this->getFromModelMemory($key)){
			return $correlations;
		}
		$qb = $this->predictors()->take($limit);
		if($variableCategoryName){
			$cat = QMVariableCategory::find($variableCategoryName);
			$qb->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID, $cat->getId());
		}
		$correlations = $qb->get();
		return $this->setInModelMemory($key, $correlations);
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return UserVariableRelationship[]|Collection
	 */
	public function getOutcomes(int $limit = null, string $variableCategoryName = null): Collection{
		$noLimitKey = __FUNCTION__ . "-category-$variableCategoryName-limit-";
		/** @var Collection $correlations */
		if($correlations = $this->getFromModelMemory($noLimitKey)){
			return $correlations->take($limit);
		}
		$key = $noLimitKey . $limit;
		if($correlations = $this->getFromModelMemory($key)){
			return $correlations;
		}
		$qb = $this->outcomes()->take($limit);
		if($variableCategoryName){
			$cat = QMVariableCategory::find($variableCategoryName);
			$qb->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID, $cat->getId());
		}
		$correlations = $qb->get();
		return $this->setInModelMemory($key, $correlations);
	}
	public function getSankeyChart(): SankeyQMChart{
		return new CorrelationsSankeyQMChart($this);
	}
	public function getNetworkGraphChart(): CorrelationsNetworkGraphQMChart{
		return new CorrelationsNetworkGraphQMChart($this);
	}
	/**
	 * @param string|null $recalculateStudyUrl
	 * @return string
	 */
	public function getDataAnalysisParagraph(string $recalculateStudyUrl = null): string{
		if(stripos($recalculateStudyUrl, 'utopia') !== false){
			le($recalculateStudyUrl);
		}
		$button = $this->getSettingsButtonHtml($recalculateStudyUrl);
		$heading = $this->getDisplayNameAttribute() . " Pre-Processing";
		$paragraph = $this->getMinimumAllowedValueSentence() . $this->getMaximumAllowedValueSentence() .
			$this->getFillingValueSentence();
		return "
            <h4 class=\"text-2xl font-semibold\">$heading</h4>
            <p>
                $paragraph
            </p>
            $button
        ";
	}
	/**
	 * @param string|null $fromUrl
	 * @return string
	 */
	public function getSettingsButtonHtml(string $fromUrl = null): string{
		$b = $this->getSettingsButton();
		if($fromUrl){
			$b->setParam('fromUrl', $fromUrl);
		}
		return $b->getCenteredRoundOutlineWithIcon();
	}
	public function getSettingsButton(): VariableSettingsStateButton{
		$b = new VariableSettingsStateButton($this);
		return $b;
	}
}
