<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLabIndex;
use App\Buttons\Model\ModelButton;
use App\Models\BaseModel;
use App\Widgets\BaseWidget;
use App\Widgets\OverTimeCardChartWidget;
use App\Widgets\ProgressBox;
class DataLabSelectQueryButton extends ModelButton {
	public $table;
	public $fieldForChartXAxis;
	/**
	 * @param BaseModel|string $tableOrModel
	 */
	public function __construct($tableOrModel, array $queryParams){
		parent::__construct($tableOrModel);
		$m = $this->getModel();
		$this->setUrl(BaseModel::generateDataLabIndexUrl($queryParams, $m->getTable()));
		$this->setParameters($queryParams);
	}
	/** @noinspection PhpUnused */
	public function getOverTimeChartWidgetParams(): array{
		$widget = $this->populateWidget(new OverTimeCardChartWidget());
		return OverTimeCardChartWidget::getComponentViewParams($widget->toArray());
	}
	public function getProgressBoxWidgetParams(): array{
		$widget = $this->populateWidget(new ProgressBox());
		return ProgressBox::getComponentViewParams($widget->toArray());
	}
	/**
	 * @param BaseWidget $box
	 * @return BaseWidget
	 */
	protected function populateWidget(BaseWidget $box): BaseWidget{
		$box->queryParams = $this->parameters;
		$box->table = $this->table;
		$box->fieldForChartXAxis = $this->fieldForChartXAxis;
		$box->title = $this->getTitleAttribute();
		$box->description = $this->tooltip;
		$box->icon = $this->getFontAwesome();
		$box->color = $this->getBackgroundColor();
		return $box;
	}
	/**
	 * @param string $table
	 * @param array $params
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function whereTable(string $table, array $params = []){
		return new static($table, $params);
	}
	/**
	 * @return ProgressBox|BaseWidget
	 */
	public function getProgressBoxWidget(): ProgressBox{
		$box = $this->populateWidget(new ProgressBox());
		return $box;
	}
	/**
	 * @return OverTimeCardChartWidget|BaseWidget
	 */
	public function getOverTimeChartWidget(): OverTimeCardChartWidget{
		$box = $this->populateWidget(new OverTimeCardChartWidget());
		return $box;
	}
}
