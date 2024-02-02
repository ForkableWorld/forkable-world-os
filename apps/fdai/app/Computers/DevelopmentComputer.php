<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Computers;
use App\Utils\ReleaseStage;
class DevelopmentComputer extends AbstractApiComputer {
	public function __construct(){
		parent::__construct();
		$this->displayName = $_SERVER["NAME"];
		$this->host = ThisComputer::LOCAL_ORIGIN;
		$this->ip = ThisComputer::getCurrentServerExternalIp();
		$this->port = 2222;
		$this->password = $this->user = ThisComputer::user();
		$this->url = \App\Utils\Env::getAppUrl();
	}
	public static function getReleaseStage(): string{
		return ReleaseStage::DEVELOPMENT;
	}
	public function isWeb(): bool{ return true; }
	public function getWebHostname(): string{
		return ThisComputer::LOCAL_ORIGIN;
	}
}
