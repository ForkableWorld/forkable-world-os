<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UI;
use App\Buttons\Links\HelpButton;
use App\Buttons\QMButton;
use App\Cards\QMCard;
use App\Logging\QMLog;
use App\Notifications\MiroNotification;
use App\Slim\Middleware\QMAuth;
use App\UI;
use App\Utils\AppMode;
use RealRashid\SweetAlert\Toaster;
class Alerter {
	/**
	 * @return Toaster
	 */
	public static function toaster(): Toaster{
		return alert();
	}
	public static function toastWithHtml(string $title, string $html = null, string $fontAwesome = null){
		if(!class_exists('alert')) {
			QMLog::info("Class alert does not exist to report: ".$title);
			return;
		}
		$t = self::popupWithHtml($title, $html, $fontAwesome);
		$t->toast($title);
	}
	public static function popupWithHtml(string $title, string $html, string $fontAwesome = null): Toaster{
		$t = self::toaster();
		if($fontAwesome){
			$t->iconHtml(UI\FontAwesome::html($fontAwesome));
		}
		$t->timerProgressBar()->autoClose(30000)->html($title, $html);
		return $t;
	}
	public static function popupWithButton(string $title, string $url, string $buttonTitle, string $buttonIcon = null,
		string $largeIcon = null): void {
		$b = new QMButton();
		$b->setUrl($url);
		$b->setTextAndTitle($buttonTitle);
		$b->setFontAwesome($buttonIcon);
		$html = $b->getLinkButtonWithIcon();
		self::popupWithHtml($title, $html, $largeIcon ?? $buttonIcon);
	}
	public static function toastWithButton(string $title, string $url, string $buttonTitle,
		string $fontAwesome = FontAwesome::INFO_CIRCLE_SOLID, string $largeIcon = null){
		$b = new QMButton();
		$b->setUrl($url);
		$b->setTextAndTitle($buttonTitle);
		$b->setFontAwesome($fontAwesome);
		$html = $b->getLinkButtonWithIcon();
		$b->logLink();
		self::toastWithHtml($title, $html, $largeIcon ?? $fontAwesome);
	}
	public static function toast(string $title, int $seconds = 5, string $fontAwesome = FontAwesome::INFO_CIRCLE_SOLID,
		string $level = MiroNotification::LEVEL_INFO, string $url = null): void {
		if(!class_exists('alert')) {
			QMLog::info("Class alert does not exist to report: ".$title);
			return;
		}
		alert()->toast($title, $fontAwesome)->autoClose(1000 * $seconds);
		if(AppMode::isAstral()){
			if($user = QMAuth::getUser()){
				try {
					$user->notifyNow(new MiroNotification($title, $level, $fontAwesome, $url));
				} catch (\Throwable $e) {
					if(AppMode::isUnitOrStagingUnitTest()){
						le($e);
					}
					QMLog::info(__METHOD__.": ".$e->getMessage());
				}
			}
		}
	}
	public static function errorToast(string $title, string $url = null, int $seconds = 5): void {
		self::toast($title, $seconds, FontAwesome::ERROR, MiroNotification::LEVEL_ERROR, $url);
	}
	public static function flashOverlayWithButton(string $title, string $url, string $buttonTitle,
		string $fontAwesome = null){
		$b = new QMButton();
		$b->setUrl($url);
		$b->setTextAndTitle($buttonTitle);
		if($fontAwesome){
			$b->setFontAwesome($fontAwesome);
		}
		$html = $b->getLinkButtonWithIcon();
		flash()->overlay($html, $title);
	}
	public static function error(string $message){
		self::flashOverlayWithButton($message, HelpButton::url(), "Need Help?", FontAwesome::ERROR);
	}
	public static function errorWithHelpButtonToast(string $err){
		Alerter::toastWithButton($err, HelpButton::url(), "Get Help");
	}
	public static function push(string $title, string $url, string $buttonTitle, int $userId = null,
		string $image = null){
		$c = new QMCard();
		$c->setTitle($title);
		$c->setUrl($url);
		$c->push($userId);
	}
}
