<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Computers;
use App\Utils\ReleaseStage;
class WordPressComputer extends AbstractWebComputer {
	use OnLightsail;
	public function getHealthCheckPaths(): array{
		return []; // urgent save paths in node envs
	}
	public function getSiteMap(): string{
		return $this->getUrl()."/sitemap.xml";
	}
	public function deploy(): void {
		// TODO: Implement deploy() method.
	}
	public function getWebHostname(): string{
		// TODO: Implement getWebHostname() method.
	}
	public static function getReleaseStage(): string{
		return ReleaseStage::PRODUCTION;
	}
	protected static function healthCheckString(): string{
		return "WordPress";
	}
	public static function getBlueprintSnapshot(): LightsailSnapshot{
		return PhpUnitComputer::getBlueprintSnapshot();
	}
}
