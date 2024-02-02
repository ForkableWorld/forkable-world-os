<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Forms;
abstract class AirTableFormPage extends AbstractFormPage {
	public function getPageURL(): string{
		return "https://airtable.com/" . $this->getFormId();
	}
	abstract protected function getFormId(): string;
}
