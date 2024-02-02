<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Variables\QMVariable;
class StudiesQMCard extends QMCard {
	/**
	 * @param QMVariable $variable
	 */
	public function __construct($variable = null){
		$studies = $variable->getBestStudies();
		$html = '';
		foreach($studies as $study){
			$html .= $study->getTitleGaugesTagLineHeader(true, true);
		}
		$this->setHtmlContent($html);
		parent::__construct("Best " . $variable->getVariableName() . " Studies");
	}
}
