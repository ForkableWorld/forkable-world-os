<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Traits\FileTraits\IsSolution;
use Facade\IgnitionContracts\RunnableSolution;
abstract class ModelSolution extends AbstractSolution implements RunnableSolution {
	use IsSolution;
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
	public function getDocumentationLinks(): array{
		$m = $this->getBaseModel();
		foreach($m->getButtons() as $button){
			$this->links[$button->getTitleAttribute()] = $button->getUrl();
		}
		return $this->links;
	}
	/**
	 * @return BaseModel
	 */
	public function getBaseModel(): BaseModel{
		return $this->model;
	}
	public function getRunParameters(): array{
		$m = $this->getBaseModel();
		return [
			'table' => $m->getTable(),
			'class' => $m->getShortClassName(),
			'id' => $m->getId(),
		];
	}
	public function getShortClassName(): string{
		return $this->getBaseModel()->getClassNameTitle();
	}
}
