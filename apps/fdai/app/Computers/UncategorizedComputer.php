<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Computers;
class UncategorizedComputer extends JenkinsSlave {
	public function isWeb(): bool{
		return false;
	}
	public function needToReboot(): ?string{
		return false;
	}
	public static function instancePrefix(): string{
		return "";
	}
}
