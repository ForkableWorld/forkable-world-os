<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Traits;
use App\Buttons\Analyzable\DataLabFailedAnalysesButton;
use App\Models\UserVariableRelationship;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\Widgets\OverTimeCardChartWidget;
trait HasErrors {
	public function getInternalErrorMessageLink(int $maxLength = 140): ?string{
		$internal = $this->getAttribute('internal_error_message');
		if(empty($internal)){
			return null;
		}
		if(stripos($internal, '<a ') !== false){
			return $internal;
		}
		$url = $this->getInternalErrorUrl();
		$truncated = $this->getTruncatedUserErrorMessageText($maxLength);
		if(!$truncated){
			$truncated = QMStr::truncate($internal, $maxLength);
		}
		$tooltip = $this->getUserErrorMessageTooltip();
		if(!$tooltip){
			$tooltip = $internal;
		}
		return HtmlHelper::generateLink($truncated ?? "Ignition Error Report", $url, true, $tooltip);
	}
	public function getUserErrorMessageLink(int $maxLength = 140): ?string{
		$errors = '';
		$user = $this->getAttribute('user_error_message');
		if($e = $this->getAttribute('errors')){
			$errors .= "errors: \n" . $e . "\n";
		}
		if($e = $this->getAttribute('error')){
			$errors .= "error: \n" . $e . "\n";
		}
		if($e = $this->getAttribute('internal_error_message')){
			$errors .= "internal_error_message:  \n" . $e . "\n";
		}
		if($e = $this->getAttribute('user_error_message')){
			$errors .= "user_error_message: \n" . $e . "\n";
		}
		if($e = $this->getAttribute('connect_error')){
			$errors .= "connect_error: \n" . $e . "\n";
		}
		if($e = $this->getAttribute('update_error')){
			$errors .= "update_error: \n" . $e . "\n";
		}
		if(empty($errors)){
			return null;
		}
		$url = $this->getUrl();
		$e = HtmlHelper::stripHtmlTags($e);
		$truncated = QMStr::truncate($e, $maxLength);
		return HtmlHelper::generateLink($truncated, $url, true, QMStr::truncate($e, 500));
	}
	public static function generateErrorsIndexUrl(): string{
		return static::generateDataLabIndexUrl([QMRequest::PARAM_ERRORED => true]);
	}
	protected function getUserErrorMessagePlainText(): ?string{
		$user = $this->getAttribute('user_error_message');
		if(empty($user)){
			return null;
		}
		$user = HtmlHelper::stripHtmlTags($user);
		return $user;
	}
	/**
	 * @param int $maxLength
	 * @return string
	 */
	protected function getTruncatedUserErrorMessageText(int $maxLength): ?string{
		$userErrorMessageText = $this->getUserErrorMessagePlainText();
		if(!$userErrorMessageText){
			return null;
		}
		if(!$userErrorMessageText){
			le('!$userErrorMessageText');
		}
		$truncated = QMStr::truncate($userErrorMessageText, $maxLength);
		return $truncated;
	}
	/**
	 * @return string
	 */
	protected function getUserErrorMessageTooltip(): ?string{
		$userErrorMessageText = $this->getUserErrorMessagePlainText();
		if(!$userErrorMessageText){
			return null;
		}
		$tooltip = QMStr::truncate($userErrorMessageText, 500);
		return $tooltip;
	}
	/**
	 * @return string
	 */
	protected function getInternalErrorUrl(): string{
		$internal = $this->getAttribute('internal_error_message');
		if(strpos($internal, "http") === 0){
			$url = $internal;
		} else{
			$url = $this->getUrl();
		}
		return $url;
	}
	/**
	 * @return OverTimeCardChartWidget
	 */
	public static function getErrorsOverTimeWidget(): OverTimeCardChartWidget{
		$widget = DataLabFailedAnalysesButton::whereTable(static::TABLE)->getOverTimeChartWidget();
		return $widget;
	}
	/**
	 * @param string|null $userErrorMessage
	 * @return QMAnalyzableTrait
	 */
	public function setUserErrorMessage(?string $userErrorMessage): self{
		$this->setAttribute(UserVariableRelationship::FIELD_USER_ERROR_MESSAGE, $userErrorMessage);
		return $this;
	}
	/**
	 * @return string
	 */
	public function getInternalErrorMessageHtml(): string{
		$err = $this->internalErrorMessage;
		if(!$err){
			return "";
		}
		return "
            <div id=\"internal-error-message\" class=\"alert alert-danger\">
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
                    <i class=\"material-icons\">close</i>
                </button>
                <h1 style='display: inline;'>Internal Error</h1>
                <p>$err</p>
            </div>
        ";
	}
	public function getErrorsHtml(): string{
		$cards = $this->getExceptionCards();
		$str = "";
		foreach($cards as $card){
			$str .= $card->renderBootstrap3();
			//$str .= $card->getMaterialCard();
		}
		if(QMAuth::isAdmin()){
			$str .= $this->getInternalErrorMessageHtml();
		}
		$str .= $this->getUserErrorMessageHtml();
		return $str;
	}
	/**
	 * @return string
	 */
	public function getUserErrorMessageHtml(): string{
		$err = $this->userErrorMessage;
		if(!$err){
			return "";
		}
		return "
            <div id=\"user-error-message\" class=\"alert alert-danger\">
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
                    <i class=\"material-icons\">close</i>
                </button>
                <h1 style='display: inline;'>Error</h1>
                <p>$err</p>
            </div>
        ";
	}
	/**
	 * @param string|null $m
	 * @return QMAnalyzableTrait
	 */
	public function setUserAndInternalErrorMessage(?string $m): self{
		$this->setUserErrorMessage($m)->setInternalErrorMessage($m);
		return $this;
	}
	/**
	 * @param string|null $internalErrorMessage
	 * @return QMAnalyzableTrait
	 */
	public function setInternalErrorMessage(?string $internalErrorMessage): self{
		if($internalErrorMessage){
			$this->logInfo($internalErrorMessage);
		}
		$this->setAttribute(UserVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE, $internalErrorMessage);
		return $this;
	}
	protected function truncateInternalErrorMessage(): void{
		$m = $this->getInternalErrorMessage();
		if($m){
			if(!is_string($m)){
				le("internalErrorMessage should be a string but is " . gettype($m), $m);
			}
			$max = $this->l()->getAttributeMaxLength(UserVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE);
			$this->setInternalErrorMessage(QMStr::truncate($m, $max - 1));
		}
	}
}
