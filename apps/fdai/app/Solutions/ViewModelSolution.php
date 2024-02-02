<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Models\BaseModel;
use Facade\IgnitionContracts\Solution;
use App\Slim\Model\DBModel;
class ViewModelSolution extends AbstractSolution implements Solution {
	/**
	 * @var BaseModel
	 */
	public $model;
	public $links;
	/**
	 * ModelSolution constructor.
	 * @param BaseModel|DBModel $model
	 */
	public function __construct($model = null){
		if($model){
			if($model instanceof DBModel){
				$model = $model->l();
			}
			$this->model = $model;
		}
	}
	public function getSolutionTitle(): string{
		return "View ".$this->model->getClassNameTitle();
	}
	public function getSolutionDescription(): string{
		return "Open ".$this->model->getTitleAttribute();
	}
	public function getDocumentationLinks(): array{
		return $this->model->getUrls();
	}
}
