<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** Created by PhpStorm.
 * User: m_000
 * Date: 4/12/2018
 * Time: 8:15 AM
 */
namespace App\DevOps;
use App\AppSettings\AppStatus\BuildStatus;
use App\Logging\QMLog;
use App\Properties\Base\BaseClientIdProperty;
use App\Utils\APIHelper;
use stdClass;
class GithubHelper {
	public const GITHUB_IONIC_BASE_PATH = 'https://api.github.com/repos/quantimodo/quantimodo-android-chrome-ios-web-app';
	public const GITHUB_APP_BUILDER_BASE_PATH = 'https://api.github.com/repos/mikepsinn/qm-app-builder';
	/**
	 * @param string $clientId
	 * @return mixed|object
	 */
	public static function mergeAppBuilderMasterIntoAppGitBranch($clientId){
		//$response = self::deleteGitBranch($clientId);
		$response = self::createAppBuilderGitBranch($clientId);
		$response = self::mergeGithubBranch(self::GITHUB_APP_BUILDER_BASE_PATH, "apps/$clientId", "master");
		return $response;
	}
	/**
	 * @param $repoPath
	 * @param $destinationBranchBase
	 * @param $sourceBranchHead
	 * @return mixed|stdClass
	 */
	public static function mergeGithubBranch($repoPath, $destinationBranchBase, $sourceBranchHead){
		$response = self::callGithubAPI('POST', $repoPath . '/merges', [
			"base" => $destinationBranchBase,
			"head" => $sourceBranchHead,
		]);
		if(isset($response->error) && $response->error == ""){  // It just means there was nothing to merge
			$response->message = $destinationBranchBase . " is already up to date with " . $sourceBranchHead;
			unset($response->error);
		} else{
			$response->message = "Merged $sourceBranchHead to $destinationBranchBase";
		}
		return $response;
	}
	/**
	 * @param string $currentServer
	 * @return mixed|object
	 */
	public static function githubDevelopBranchBuildStatus($currentServer = null){
		$response = self::callGithubAPI('GET', self::GITHUB_IONIC_BASE_PATH . '/commits/develop/status');
		$response->statusCode = 200;
		if($currentServer){
			$currentServer = strtolower($currentServer);
			$response->state = "success";
			$response->success = true;
			foreach($response->statuses as $status){
				$lowerCaseContext = strtolower($status->context);
				if(!str_contains($lowerCaseContext, $currentServer) && $status->state !== "success" &&
				   !str_contains($lowerCaseContext, 'debug')){
					$response->state = $status->state;
				}
			}
		}
		if(!isset($response->state)){
			QMLog::error("No state in github response", ['github response' => $response]);
			return $response;
		}
		if($response->state !== "success"){
			$response->message = "Develop branch test status is $response->state!  See https://goo.gl/7MZQ7H";
			$response->success = false;
			//$response->statusCode = 400;
			QMLog::error($response->message, ['github response' => $response]);
		} else{
			$response = self::mergeIonicDevelopIntoMaster();
			BuildStatus::triggerBuildsForAllApps();
		}
		unset($response->repository);
		$fullResponse['developTestStatuses'] = $response;
		if(isset($fullResponse['developTestStatuses']->state) &&
			$fullResponse['developTestStatuses']->state === "success"){
			$fullResponse['mergeResponse'] = self::mergeIonicDevelopIntoMaster();
			$fullResponse['buildTriggerResponse'] = BuildStatus::triggerBuildsForAllApps();
		}
		return $fullResponse;
	}
	/**
	 * @return mixed|object
	 */
	public static function mergeIonicDevelopIntoMaster(){
		$response = self::mergeGithubBranch(self::GITHUB_IONIC_BASE_PATH, "master", "develop");
		return $response;
	}
	/**
	 * @param $clientId
	 * @return string
	 */
	public static function getIonicAppBranchName($clientId){
		return 'apps/' . $clientId;
	}
	/**
	 * @param $clientId
	 * @return string
	 */
	public static function getUrlEncodedBranchName($clientId){
		return urlencode(self::getIonicAppBranchName($clientId));
	}
	/**
	 * @return string
	 */
	public static function getGithubAccessTokenString(){
		return '?access_token=' . \App\Utils\Env::get('GITHUB_ACCESS_TOKEN');
	}
	/**
	 * @param $clientId
	 * @return mixed|stdClass|void
	 */
	public static function createAppBuilderGitBranch(string $clientId){
		if(BaseClientIdProperty::isTestClientId($clientId)){
			return;
		}
		$appBuilderRefsPath = self::GITHUB_APP_BUILDER_BASE_PATH . '/git/refs';
		$appBuilderMasterBranch = self::GITHUB_APP_BUILDER_BASE_PATH . '/git/refs/heads/master';
		$developBranch = self::callGithubAPI('GET', $appBuilderMasterBranch);
		if(!isset($developBranch->object)){
			QMLog::error("Could not create app branch", ['github response' => $developBranch]);
			return $developBranch;
		}
		return self::callGithubAPI('POST', $appBuilderRefsPath, [
			"ref" => "refs/heads/apps/$clientId",
			"sha" => $developBranch->object->sha,
		]);
	}
	/**
	 * @param $clientId
	 * @return mixed|stdClass
	 */
	public static function deleteGitBranch($clientId){
		$appBuilderRefsPath = self::GITHUB_APP_BUILDER_BASE_PATH . '/git/refs';
		$branchToDelete = $appBuilderRefsPath . '/heads/apps/' . $clientId;
		return self::callGithubAPI('DELETE', $branchToDelete);
	}
	/**
	 * @param $method
	 * @param $url
	 * @param bool $data
	 * @return mixed|stdClass
	 */
	public static function callGithubAPI($method, $url, $data = false){
		return APIHelper::callAPI($method, $url . self::getGithubAccessTokenString(), $data, null, "mikepsinn");
	}
    public static function enabled(): bool{
        $t = \App\Utils\Env::get('GITHUB_ACCESS_TOKEN');
        return !empty(\App\Utils\Env::get('GITHUB_ACCESS_TOKEN')) && stripos($t, "disabled") === false;
    }
}
