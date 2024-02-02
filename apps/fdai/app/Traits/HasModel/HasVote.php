<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\Vote;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasVote {
	public function getVoteId(): int{
		$nameOrId = $this->getAttribute('vote_id');
		return $nameOrId;
	}
	public function getVoteButton(): QMButton{
		$vote = $this->getVote();
		if($vote){
			return $vote->getButton();
		}
		return Vote::generateDataLabShowButton($this->getVoteId());
	}
	/**
	 * @return Vote
	 */
	public function getVote(): Vote{
		if($this instanceof BaseProperty && $this->parentModel instanceof Vote){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('vote')){
			return $l;
		}
		$id = $this->getVoteId();
		$vote = Vote::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['vote'] = $vote;
		}
		if(property_exists($this, 'vote')){
			$this->vote = $vote;
		}
		return $vote;
	}
	public function getVoteNameLink(): string{
		return $this->getVote()->getDataLabDisplayNameLink();
	}
	public function getVoteImageNameLink(): string{
		return $this->getVote()->getDataLabImageNameLink();
	}
}
