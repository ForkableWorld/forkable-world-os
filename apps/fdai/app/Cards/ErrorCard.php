<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Buttons\QMButton;
use App\Exceptions\ExceptionHandler;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class ErrorCard extends QMCard {
	public $fontAwesome = FontAwesome::ERROR;
	public $image = ImageUrls::ERROR_MESSAGE;
	public $subTitle = "Warning";
	/**
	 * ErrorCard constructor.
	 * @param string $title
	 * @param string $message
	 * @param QMButton[] $buttons
	 */
	public function __construct(string $title, string $message, array $buttons){
		parent::__construct();
		$this->setTitle($title);
		$this->setContent($message);
		$this->buttons = $buttons;
	}
	/**
	 * @param array $exceptions
	 * @return self[]
	 */
	public static function fromExceptions(array $exceptions): array{
		$cards = [];
		foreach($exceptions as $e){
			$cards[] = self::fromException($e);
		}
		return $cards;
	}
	/**
	 * @param \Throwable $e
	 * @return static
	 */
	public static function fromException(\Throwable $e): self{
		$title = QMStr::toShortClassName(get_class($e));
		$title = str_replace("Exception", "", $title);
		$title = QMStr::classToTitle($title);
		$buttons = ExceptionHandler::getDocumentationLinkButtons($e);
		$card = new static($title, $e->getMessage(), $buttons);
		return $card;
	}
}
