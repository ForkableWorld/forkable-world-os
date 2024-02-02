<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Http\Parameters\DebugRequestParam;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
class ReleaseStage {
	public const DEVELOPMENT = 'development';
	public const TESTING = 'testing';
	public const DEBUG = 'debug';
	public const PRODUCTION = 'production';
	public const STAGING = 'staging';
	/**
	 * @param null $userId
	 * @return null|string
	 */
	public static function getReleaseStage($userId = null): string{
		$env = strtolower(\App\Utils\Env::get('APP_ENV'));
		if(stripos($env, Env::ENV_STAGING_REMOTE) !== false || stripos($env, ReleaseStage::TESTING) !== false){
			return ReleaseStage::TESTING;
		}
		if(stripos($env, ReleaseStage::DEVELOPMENT) !== false || stripos($env, Env::ENV_LOCAL) !== false){
			return ReleaseStage::DEVELOPMENT;
		}
		if(stripos($env, ReleaseStage::STAGING) !== false){
			return ReleaseStage::STAGING;
		}
		if(stripos($env, ReleaseStage::PRODUCTION) !== false){
			return ReleaseStage::PRODUCTION;
		}
		if(AppMode::isTravisOrHeroku()){
			return ReleaseStage::TESTING;
		}
		if(DebugRequestParam::isDebug()){
			return ReleaseStage::DEBUG;
		}
		$userEmail = null;
		$u = QMAuth::getQMUserIfSet();
		if($u && isset($u->id)){
			$userId = $u->id;
		}
		if($u && isset($u->email)){
			$userEmail = $u->email;
		}
		if(QMUser::isTestUserByIdOrEmail($userId, $userEmail)){
			return ReleaseStage::TESTING;
		}
		if($_SERVER["PHP_SELF"] === "artisan"){
			return ReleaseStage::PRODUCTION;
		}
		$host = QMRequest::host();
		if($host){
			if(stripos($host, 'local') !== false){
				return ReleaseStage::DEVELOPMENT;
			}
			if(stripos($host, ReleaseStage::STAGING) !== false){
				return ReleaseStage::STAGING;
			}
			if(stripos($host, ReleaseStage::PRODUCTION) !== false){
				return ReleaseStage::PRODUCTION;
			}
		}
		return "unknown";
	}
}
