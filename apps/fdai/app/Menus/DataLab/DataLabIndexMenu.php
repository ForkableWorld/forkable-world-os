<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Menus\DataLab;
use App\Buttons\Analyzable\DataLabAnalysisProgressButton;
use App\Buttons\Analyzable\DataLabFailedAnalysesButton;
use App\Buttons\Analyzable\DataLabNeverAnalyzedButton;
use App\Buttons\DataLabIndex\DataLabAscendingIndexButton;
use App\Buttons\DataLabIndex\DataLabDescendingIndexButton;
use App\Buttons\DataLabIndex\DataLabMostRecentIndexButton;
use App\Buttons\DataLabIndex\DataLabTrashButton;
use App\Buttons\DataLabIndex\LeastRecentIndexButtonAscendingIndexButton;
use App\Buttons\QMButton;
use App\Menus\BaseModelMenu;
use App\Properties\Base\BaseAnalysisEndedAtProperty;
use App\UI\FontAwesome;
class DataLabIndexMenu extends BaseModelMenu {
	/**
	 * @return QMButton[]
	 */
	public function getButtons(): array{
		if($this->buttons){
			return $this->buttons;
		}
		$m = $this->getModel();
		$this->addButton(new DataLabTrashButton($m));
		if($m->hasColumn(BaseAnalysisEndedAtProperty::NAME)){
			$this->addDataLabAnalysisButtons();
		}
		// Too many buttons $this->addCountSortButtons();
		// Too many buttons $this->addDateSortButtons();
		return $this->buttons;
	}
	public function getTitleHtml(): string{
		return FontAwesome::html($this->fontAwesome);
	}
	/**
	 * @return static
	 */
	public function addDataLabAnalysisButtons(): DataLabIndexMenu{
		$buttons = [];
		$model = $this->getModel();
		$buttons[] = new DataLabAnalysisProgressButton($model);
		$buttons[] = new DataLabNeverAnalyzedButton($model);
		$buttons[] = new DataLabFailedAnalysesButton($model);
		return $this->addButtons($buttons);
	}
	/**
	 * @return static
	 */
	public function addDateSortButtons(): DataLabIndexMenu{
		$buttons = [];
		$model = $this->getModel();
		$countFields = $model->getDates();
		foreach($countFields as $field){
			$buttons[] = new DataLabMostRecentIndexButton($model, $field);
			$buttons[] = new LeastRecentIndexButtonAscendingIndexButton($model, $field);
		}
		return $this->addButtons($buttons);
	}
	/**
	 * @return static $this
	 */
	public function addCountSortButtons(): DataLabIndexMenu{
		$buttons = [];
		$model = $this->getModel();
		$countFields = $model->getCountFields();
		foreach($countFields as $field){
			$buttons[] = new DataLabAscendingIndexButton($model, $field);
			$buttons[] = new DataLabDescendingIndexButton($model, $field);
		}
		return $this->addButtons($buttons);
	}
}
