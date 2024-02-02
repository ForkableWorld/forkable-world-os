<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Computers;
use App\DevOps\QMServices;
use Exception;
class RedisComputer extends AbstractApiComputer {
	use OnLightsail;
	const NAME = 'redis';
	const TAG_REDIS = 'redis';
	public function needToReboot(): ?string{
		try {
			$healthy = QMServices::checkRedis();
		} catch (Exception $e) {
			return true;
		}
	}
	public function isWeb(): bool{
		return false;
	}
	public static function tags(): array{
		return [self::TAG_REDIS => true];
	}
	public static function getBlueprintSnapshot(): LightsailSnapshot{
		// TODO: Implement getBlueprintSnapshot() method.
	}
	/**
	 * @return static
	 */
	public static function first(): JenkinsSlave{
		$i = LightsailInstanceResponse::findInMemoryOrApi(self::NAME);
		return $i->getComputer();
	}
	/**
	 * @throws \Exception
	 */
	public function assertHealthy(){
		parent::assertHealthy();
		QMServices::checkRedis();
	}
}
