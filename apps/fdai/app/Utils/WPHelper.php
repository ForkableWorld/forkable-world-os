<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Exceptions\ExceptionHandler;
use App\Logging\QMLog;
use Exception;
class WPHelper {
	/**
	 * WPHelper constructor.
	 * @param null $userId
	 */
	public function __construct($userId = null){
		WPHelper::loadWordPress($userId);
	}
	/**
	 * @param array $recipientIdsOrUserNames Can be an array of usernames, user_ids or mixed.
	 * @param string $subject
	 * @param string $content
	 * @return bool|string
	 */
	public function sendMessage(array $recipientIdsOrUserNames, string $subject, string $content){
		bp_setup_globals();
		$send = messages_new_message([
			'recipients' => $recipientIdsOrUserNames,
			'subject' => $subject,
			'content' => $content,
			'error_type' => 'wp_error',
		]);
		if(true === is_int($send)){
			$result = true;
		} else{
			$result = $send->get_error_message();
		}
		return $result;
	}
	public function installBuddyPress(){
		bp_core_install([
			'notifications' => true,
			'friends' => true,
			'groups' => true,
			'messages' => true,
			'xprofile' => true,
			'blogs' => true,
		]);
	}
	/**
	 * @param $userId
	 */
	public function getMessages($userId){
		wp_set_current_user($userId);
		bp_setup_globals();
	}
	/**
	 * @return bool
	 */
	public static function wpInstalled(){
		$realPath = realpath(__DIR__ . '/../../../../public/wp/wp-load.php');
		$exists = file_exists($realPath);
		if(!$exists){
			QMLog::error($realPath . " does not exist!");
		}
		return $exists;
	}
	/**
	 * @param $userId
	 * @return bool
	 */
	public static function loadWordPress(int $userId = null): bool{
		if(!isset($_SERVER['SERVER_PROTOCOL'])){
			$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
		}
		$realPath = realpath(__DIR__ . '/../../../../wp/wp-load.php');
		$exists = self::wpInstalled();
		if(AppMode::isTravisOrHeroku()){
			QMLog::warning("Can't load wp-load on Travis because Cannot modify header information error in testing");
			return false;
		}
		if(!$exists){
			return false;
		}
		\App\Logging\ConsoleLog::info($realPath . " exists!");
		try { // Avoid Cannot modify header information error in testing
			require_once $realPath;
		} catch (Exception $e) {
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
			return false;
		}
		if($userId){
			wp_set_current_user($userId);
		}
		return true;
	}
}
