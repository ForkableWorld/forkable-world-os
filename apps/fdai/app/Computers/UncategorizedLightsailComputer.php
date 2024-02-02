<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Computers;
class UncategorizedLightsailComputer extends JenkinsSlave {
	use OnLightsail;
	public function isWeb(): bool{
		return false;
	}
	public function needToReboot(): ?string{
		return false;
	}
	public static function instancePrefix(): string{
		return "";
	}
	public static function tags(): array{
		// TODO: Implement tags() method.
	}
	public static function getBlueprintSnapshot(): LightsailSnapshot{
		// TODO: Implement getBlueprintSnapshot() method.
	}
}
