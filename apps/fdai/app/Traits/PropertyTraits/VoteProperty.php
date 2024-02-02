<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\Vote;
use App\Traits\HasModel\HasVote;
trait VoteProperty {
	use HasVote;
	public function getVoteId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getVote(): Vote{
		return $this->getParentModel();
	}
}
