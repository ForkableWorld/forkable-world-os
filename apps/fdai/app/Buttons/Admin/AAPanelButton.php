<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
use App\Computers\ThisComputer;
use App\Exceptions\NoInternetException;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class AAPanelButton extends AdminButton {
	public const RUNNER_IPS = [
		'datacenter' => '192.168.1.175',
		'essentials' => '192.168.1.29',
		'envy' => '192.168.1.24',
		'infinity' => '192.168.1.146',
		'mac' => '192.168.1.44',
		'OptiPlex-360' => '192.168.1.12',
		'sonicmaster' => '192.168.1.245',
		'XE3' => '192.168.1.113',
		'xe3' => '192.168.1.113',
		'aapanel-works-1' => '34.74.40.182',
		'surface8' => '192.168.1.194',
		'worker-spot-instance-ubuntu-22' => 'worker.quantimo.do',
	];
	public $accessibilityText = 'AAPanel Server admin panel';
	public $color = '#3467d6';
	public $fontAwesome = FontAwesome::DATABASE_SOLID;
	public $id = 'admin-aapanel-button';
	public $image = ImageUrls::DEVELOPMENT_019_DATABASES_2;
	public $parameters = [];
	public $target = 'self';
	public $text = 'AAPanel';
	public $title = 'AAPanel';
	public $tooltip = 'AAPanel Server admin panel';
	public $visible = true;
	/**
	 * @throws NoInternetException
	 */
	public function __construct(){
		$URL = ThisComputer::getAAPanelUrl();
		$this->setUrl($URL);
		parent::__construct($this->text ." on ". ThisComputer::getComputerName());
	}
}
