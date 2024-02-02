<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\Application;
use App\Models\BaseModel;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasApplication {
	public function getApplicationId(): int{
		$nameOrId = $this->getAttribute('application_id');
		return $nameOrId;
	}
	public function getApplicationButton(): QMButton{
		$application = $this->getApplication();
		if($application){
			return $application->getButton();
		}
		return Application::generateDataLabShowButton($this->getApplicationId());
	}
	/**
	 * @return Application
	 */
	public function getApplication(): Application{
		if($this instanceof BaseProperty && $this->parentModel instanceof Application){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('application')){
			return $l;
		}
		$id = $this->getApplicationId();
		$application = Application::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['application'] = $application;
		}
		if(property_exists($this, 'application')){
			$this->application = $application;
		}
		return $application;
	}
	public function getApplicationNameLink(): string{
		return $this->getApplication()->getDataLabDisplayNameLink();
	}
	public function getApplicationImageNameLink(): string{
		return $this->getApplication()->getDataLabImageNameLink();
	}
}
