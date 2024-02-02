<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\Buttons\QMButton;
use App\Exceptions\InvalidStringException;
trait HasUserProfilePage {
	abstract protected function getUserProfilePageUrl(): ?string;
	/**
	 * @param array $params
	 * @return \App\Buttons\QMButton
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	public function getUserProfilePageButton(array $params = []):QMButton{
		$b = new QMButton();
		$b->setUrl($this->getUserProfilePageUrl(), $params);
		$b->setTextAndTitle($this->getTitleAttribute());
		$b->setImage($this->getImage());
		$b->setFontAwesome($this->getFontAwesome());
		return $b;
	}
}
