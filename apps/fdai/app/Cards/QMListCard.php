<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Slim\Model\DBModel;
use Dialogflow\Action\Questions\ListCard;
class QMListCard extends ListCard {
	public $id;
	public $type;
	/**
	 * QMListCard constructor.
	 * @param DBModel $model
	 */
	public function __construct($model){
		$this->title($model->getListCardTitle());
		$this->id = $model->getId();
		$this->type = $model->getShortClassName();
		$buttons = $model->getButtons();
		foreach($buttons as $button){
			$this->addOption($button->getOption());
		}
	}
}
