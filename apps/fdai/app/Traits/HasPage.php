<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\QMButton;
use App\Pages\BasePage;
trait HasPage {
	abstract public function getPage(): BasePage;
	public function getHtml(): string{
		return $this->getPage()->getHtml();
	}
	abstract public function getBody(): string;
	public function getButton(): QMButton{
		return $this->getPage()->getButton();
	}
	public function getUrl(): string{
		return $this->getPage()->getUrl();
	}
}
