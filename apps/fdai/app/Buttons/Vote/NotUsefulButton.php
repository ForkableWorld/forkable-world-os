<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Vote;
use App\Buttons\QMButton;
use App\Cards\QMCard;
use App\UI\FontAwesome;
use App\UI\IonIcon;
class NotUsefulButton extends QMButton {
	public $fontAwesome = FontAwesome::THUMBS_DOWN;
	/**
	 * NotUsefulButton constructor.
	 * @param QMCard $card
	 */
	public function __construct($card){
		$this->setParameters([
			'cardId' => $card->getId(),
			'useful' => null,
		]);
		parent::__construct("Useful?", null, null, IonIcon::thumbsup);
	}
	public function fulfillIntent(){
	}
}
