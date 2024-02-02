<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Models\BaseModel;
class EditModelSolution extends ViewModelSolution {
	/**
	 * @var BaseModel
	 */
	public $model;
	public $links;
	public function getSolutionTitle(): string{
		return "Edit ".$this->model->getClassNameTitle();
	}
	public function getSolutionDescription(): string{
		return "Edit ".$this->model->getTitleAttribute();
	}
	public function getDocumentationLinks(): array{
		$model = $this->model;
		if($model->hasValidId()){
			return ['Edit' => $model->getDataLabEditUrl()];
		} else{
			return ['No ID for URL' => ""];
		}
	}
}
