<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
use App\Computers\JenkinsSlave;
use App\Computers\ThisComputer;
abstract class DebugButton extends AdminButton {
	protected JenkinsSlave $computer;
	public function __construct(JenkinsSlave $c = null){
		parent::__construct();
		if(!$c){$c = ThisComputer::instance();}
		$this->computer = $c;
		$url = $c->getUrl();
		$this->setUrl($url . $this->getPath());
	}
	abstract protected function getPath(): string;
}
