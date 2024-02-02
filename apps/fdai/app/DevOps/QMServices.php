<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps;
use App\Console\Kernel;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Traits\CanBeCalledStatically;
use Event;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Collection;
use PragmaRX\Health\Commands;
use PragmaRX\Health\Http\Controllers\Health as HealthController;
use PragmaRX\Health\Service;
use PragmaRX\Health\Support\Resource;
class QMServices extends Commands {
	use CanBeCalledStatically;
	const FILEBEAT = 'filebeat';
	const MYSQL = 'mysql';
	const NGINX = 'nginx';
	const PHP74_FPM = 'php7.4-fpm';
	const REDIS = 'redis';
	private static $panel;
	/**
	 * @var Command|null
	 */
	private ?Command $command = null;
	public function __construct(){
		//$this->command = new Command();
		parent::__construct(self::healthService());
	}
	/**
	 * @throws \Exception
	 */
	public static function assertHealthy(){
		self::i()->check();
	}
	//const ALL_RESOURCES = TestCase::ALL_RESOURCES;
	/**
	 * @param Command|null $command
	 * @return int
	 * @throws \Exception
	 */
	public function check(Command $command = null): int {
		try {
			$event = Kernel::artisan("health:check");
			//return parent::check($this->command = $command ?? new HealthCheckCommand());
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			self::logPanel();
			le($e);
		}
	}
	/**
	 * @param Command|null $command
	 */
	public function panel(Command $command = null): int{
		$this->outputServicesHealthPanel();
	}
	public static function assertAllResourcesHealthy(): void{
		$resources = static::getResources();
		foreach($resources as $resource){
			QMLog::info($resource);
		}
	}
	/**
	 * Check all resources.
	 * @param bool $force
	 * @return Collection
	 * @throws Exception
	 */
	public static function checkResources(bool $force = false): Collection{
		return self::healthService()->checkResources($force);
	}
	/**
	 * Check a resource.
	 * @param string $resource
	 * @return Resource
	 * @throws \Exception
	 */
	public static function checkResource(string $resource): Resource{
		/** @var Resource $res */
		$res = self::healthService()->checkResource($resource);
		return $res;
	}
	/**
	 * Check a resource.
	 * @return Resource
	 * @throws \Exception
	 */
	public static function checkDatabase(): Resource{
		$res = self::checkResource('database');
		return $res;
	}
	/**
	 * Check a resource.
	 * @return Resource
	 * @throws \Exception
	 */
	public static function checkRedis(): Resource{
		$res = self::checkResource(self::REDIS);
		if(!$res->isHealthy()){
			le("Redis not working! config: ", config('database.redis'));
		}
		return $res;
	}
	/**
	 * @return Service
	 */
	public static function healthService(): Service{
		return app('pragmarx.health');
	}
	/**
	 * @return Collection
	 */
	public static function getResources(): Collection{
		try {
			return self::healthService()->getResources();
		} catch (Exception $e) {
			le($e);
		}
	}
	/**
	 * @return HealthController
	 */
	protected static function healthController(): HealthController{
		$controller = new HealthController(self::healthService());
		return $controller;
	}
	/**
	 * @return static
	 */
	public static function i(): QMServices{
		return (new static);
	}
	public static function outputServicesHealthPanel(): void{
		Event::listen('Illuminate\Console\Events\CommandFinished', function($event){
			/** @var CommandFinished $event */
			if($event->command == 'health:panel'){
				QMLog::info(self::$panel = $event->output->fetch());
			}
		});
		Kernel::artisan("health:panel");
	}
	public static function logPanel(): void{
		if(!self::$panel){
			self::outputServicesHealthPanel();
			return;
		}
		ConsoleLog::info(self::$panel);
	}
}
