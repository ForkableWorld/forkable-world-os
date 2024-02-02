<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** Created by PhpStorm.
 * User: m_000
 * Date: 4/12/2018
 * Time: 8:13 AM
 */
namespace App\AppSettings\AppStatus;
use App\DevOps\GithubHelper;
use App\Logging\QMLog;
use App\Logging\QMLogger;
use App\Utils\APIHelper;
use stdClass;
class CircleCIHelper {
    public const CIRCLE_CI_TOKEN = "04406fa27317027fbb40ce74ded3ad1c28b3e89f";
    public const CIRCLE_CI_BASE_URL = "https://circleci.com/api/v1.1/project/github/mikepsinn/qm-app-builder";
    /**
     * @param $clientId
     * @return string
     */
    public static function getCircleCIUrlForBranch($clientId){
        return self::CIRCLE_CI_BASE_URL."/tree/".GithubHelper::getUrlEncodedBranchName($clientId).self::getCircleCITokenString();
    }
    /**
     * @return string
     */
    public static function getCircleCIAppBuilderMasterUrl(){
        return self::CIRCLE_CI_BASE_URL."tree/master".self::getCircleCITokenString();
    }
    /**
     * @return string
     */
    public static function getCircleCITokenString(){
        return "?circle-token=".self::CIRCLE_CI_TOKEN;
    }
    /**
     * @param string $clientId
     * @return mixed|object
     */
    public static function triggerBuildOnCircleCI($clientId){
	    QMLog::error("Triggering build on CircleCI");
	    self::cancelCircleCIBuildsForBranch($clientId);
	    $response = APIHelper::callAPI('POST', self::getCircleCIUrlForBranch($clientId));
	    if(!empty($response->fail_reason)){
		    return $response->fail_reason;
	    }
	    if(!empty($response->failed)){
		    return ['success' => false];
	    }
	    if(!isset($response->build_url)){
		    return $response;
        }
        return $response->build_url;
    }
    /**
     * @param $clientId
     * @return mixed|stdClass
     */
    public static function listCircleCIBuildsForBranch($clientId){
        return APIHelper::callAPI('GET', self::getCircleCIUrlForBranch($clientId));
    }
    /**
     * @param $clientId
     * @return bool|mixed|stdClass
     */
    public static function cancelCircleCIBuildsForBranch($clientId){
        $builds = self::listCircleCIBuildsForBranch($clientId);
        $response = false;
        foreach($builds as $build){
            if($build->status === "queued"){
                $response = BuddyBuildHelper::makeBuddyBuildRequest("POST", self::CIRCLE_CI_BASE_URL."/".$build->build_num."/cancel".self::getCircleCITokenString());
            }
        }
        return $response;
    }
    /**
     * @return mixed|object
     */
    public static function triggerMasterBuildOnCircleCI(){
	    QMLogger::get(__METHOD__)->error("Building ALL apps on CircleCI");
        return APIHelper::callAPI('POST', self::getCircleCIAppBuilderMasterUrl());
    }
    /**
     * @param $clientId
     * @return mixed|stdClass
     */
    public static function getCircleCILatestArifacts($clientId){ // See https://circleci.com/docs/api/v1-reference/#build-artifacts-latest
        return APIHelper::callAPI('GET', self::CIRCLE_CI_BASE_URL."/latest/artifacts".self::getCircleCITokenString()."&branch=$clientId&filter=successful", []);
    }
    public static function downloadCircleCIArifact(){
        // See https://circleci.com/docs/api/v1-reference/#download-artifact
    }
}
