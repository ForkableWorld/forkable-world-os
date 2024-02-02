<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Slim\Model\DBModel;
use Dialogflow\Action\Responses\BasicCard;
class BasicCardWithLinkedButtons extends BasicCard {
	/**
	 * @param DBModel $model
	 */
	public function __construct($model){
		$this->title($model->getListCardTitle());
		$qmButtons = $model->getButtons();
		foreach($qmButtons as $qmButton){
			$this->button($qmButton->getTitleAttribute(), $qmButton->getUrl());
		}
	}
}
