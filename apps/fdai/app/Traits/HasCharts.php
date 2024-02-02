<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\Analyzable\ChartsButton;
use App\Buttons\QMButton;
use App\Charts\ChartGroup;
use App\Charts\VariableCharts\VariableChartChartGroup;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Models\BaseModel;
use App\Repos\CCStudiesRepo;
use App\Storage\DB\ReadonlyDB;
use Illuminate\Support\Collection;
trait HasCharts {
	protected $pluckedCharts;
	abstract public function getChartGroup(): ChartGroup;
	abstract public function getUrl(array $params = []): string;
	abstract public function getTitleAttribute(): string;
	abstract public function getChartsUrl(): string;
	public function shrinkCharts(){
		$charts = $this->getChartGroup();
		$charts->shrink();
		$this->charts = $charts;
	}
	public function pluckCharts(): ?array{
		if(isset($this->attributes["charts"]) && $this->attributes["charts"] === "{}"){
			$this->pluckedCharts = false;
			return null;
		}
		$charts = $this->charts;
		if($charts && (array)$charts){
			return (array)$charts;
		}
		if($this->pluckedCharts === false){
			return null;
		}
		$qb = ReadonlyDB::getBuilderByTable($this->table);
		$plucked = $qb->where('id', $this->getId())->pluck(self::FIELD_CHARTS)->first();
		if(is_string($plucked)){
			$plucked = json_decode($plucked, true);
		}
		if(!$plucked){
			$this->pluckedCharts = false;
			return null;
		}
		return $this->pluckedCharts = $plucked;
	}
	/**
	 * @return string
	 */
	public function getChartsButtonHtml(): string{
		$b = $this->getChartsButton();
		return $b->getCenteredRoundOutlineWithIcon();
	}
	public function getChartsButton(): QMButton{
		return new ChartsButton($this);
	}
	public function getDynamicChartHtml(bool $includeJS = false): string{
		return $this->getChartGroup()->getHtmlWithDynamicCharts($includeJS);
	}
	/**
	 * @param BaseModel[]|Collection $models
	 * @return array
	 */
	public static function modelsToArraysWithoutCharts($models): array{
		$all = [];
		foreach($models as $model){
			$data = $model->toNonNullArrayFast();
			unset($data['charts']);
			$all[] = $data;
		}
		return $all;
	}
	/**
	 * @return VariableChartChartGroup
	 */
	public function getOrSetHighchartConfigs(): ChartGroup{
		$charts = $this->getChartGroup();
		if(!$charts->highchartsPopulated()){
			$charts->getOrSetHighchartConfigs();
		}
		return $this->charts = $charts;
	}
	/**
	 * @return VariableChartChartGroup
	 */
	public function setHighchartConfigs(): ChartGroup{
		$charts = $this->getChartGroup();
		try {
			$charts->setHighchartConfigs();
		} catch (NotEnoughDataException | TooSlowToAnalyzeException $e) {
			le($e);
		}
		return $this->charts = $charts;
	}
	protected function saveChartsToRepo(): void{
		$charts = $this->getChartGroup();
		foreach($charts->getChartsArray() as $chart){
			$id = $chart->getId();
			$path = $this->getStaticRepoFolder() . "/$id.js";
			try {
				$content = $chart->getOrSetHighchartConfig()->scriptContents();
				CCStudiesRepo::writeToFile($path, $content);
			} catch (NotEnoughDataException | TooSlowToAnalyzeException $e) {
				le($e);
				throw new \LogicException();
			}
		}
	}
}
