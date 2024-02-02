<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Types\QMStr;
use App\Variables\QMVariable;
class TrackingInstructionsQMCard extends QMCard {
	private $variable;
	/**
	 * @param QMVariable $variable
	 */
	public function __construct(QMVariable $variable){
		$this->variable = $variable;
		$this->setAvatar($variable->getSvgUrl());   // getSvgUrl is better because variable getImageUrl has weird dimensions
		$this->setIonIcon($variable->getIonIcon());
		$this->setImage(null);  // Dimensions all screwed up
		$dataSource = $variable->getBestDataSource();
		$this->setUrl($dataSource->getConnectWebPageUrl());
		$content = $dataSource->setInstructionsHtml($variable);
		$this->setContentAndHtmlContent($content);
		$this->content = "Record your $variable->name daily. "; // Don't use html here
		$this->buttons = $dataSource->setDefaultButtons();
		$this->title = $variable->getHowToTrackTitle();
		parent::__construct(QMStr::slugify($variable->getHowToTrackTitle()));
	}
}
