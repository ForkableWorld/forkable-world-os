<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Forms;
use Symfony\Component\DomCrawler\Form;
abstract class AbstractFormPage extends Form {
	abstract public function getPageURL(): string;
}
