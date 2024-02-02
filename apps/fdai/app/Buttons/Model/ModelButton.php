<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Model;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Reports\AnalyticalReport;
use App\Slim\Model\DBModel;
use App\Utils\AppMode;
use App\Utils\QMRoute;
class ModelButton extends QMButton {
	protected $model;
	public $table;
	/**
	 * @param BaseModel|string $tableOrModel
	 */
	public function __construct($tableOrModel = null){
		if(is_string($tableOrModel)){
			$this->table = $tableOrModel;
			$this->model = BaseModel::getInstanceByTable($tableOrModel);
		} elseif($tableOrModel instanceof AnalyticalReport){
			$this->model = $tableOrModel;
		} elseif($tableOrModel instanceof BaseModel){
			$this->table = $tableOrModel->getTable();
			$this->model = $tableOrModel;
		} elseif($tableOrModel instanceof DBModel){
			$this->table = $tableOrModel->getTable();
			// attachedOrNewLaravelModel avoids database queries just for model buttons
			$this->model = $tableOrModel->attachedOrNewLaravelModel();
		} elseif(AppMode::isApiRequest()){
			$route = QMRoute::getCurrent();
			$m = $this->model = $route->getModel();
			$this->table = $m->getTable();
		} else{
			le("Please provide model or table to " . get_class($this));
		}
		$m = $this->getModel();
		$this->title = $m->getTitleAttribute();
		$this->tooltip = $m->getSubtitleAttribute();
		$this->setImage($m->getImage());
		$this->fontAwesome = $m->getFontAwesome();
		parent::__construct();
	}
	/**
	 * @return BaseModel
	 */
	public function getModel(): ?BaseModel{
		return $this->model;
	}
}
