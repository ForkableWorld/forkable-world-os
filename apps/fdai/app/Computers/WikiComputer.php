<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Computers;
use App\DevOps\TestPath;
class WikiComputer extends AbstractWebComputer {
	public function deploy(): void {
		// TODO: Implement deploy() method.
	}
	public function getWebHostname(): string{
		// TODO: Implement getWebHostname() method.
	}
	public static function getReleaseStage(): string{
		// TODO: Implement getReleaseStage() method.
	}
	protected static function healthCheckString(): string{
		// TODO: Implement healthCheckString() method.
	}
	public function getHealthCheckPaths(): array{
		return ["/"];
	}
}
