<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Models\BaseModel;
use App\Models\Collaborator;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasCollaborator {
	public function getCollaboratorId(): int{
		$nameOrId = $this->getAttribute('collaborator_id');
		return $nameOrId;
	}
	/**
	 * @return Collaborator
	 */
	public function getCollaborator(): Collaborator{
		if($this instanceof BaseProperty && $this->parentModel instanceof Collaborator){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('collaborator')){
			return $l;
		}
		$id = $this->getCollaboratorId();
		$collaborator = Collaborator::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['collaborator'] = $collaborator;
		}
		if(property_exists($this, 'collaborator')){
			$this->collaborator = $collaborator;
		}
		return $collaborator;
	}
}
