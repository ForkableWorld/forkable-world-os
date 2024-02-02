<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Slack;
use App\Buttons\Admin\PHPStormExceptionButton;
use App\Models\Button;
use Maknz\Slack\Attachment;
use Throwable;
class SlackAttachment extends Attachment {
	/**
	 * @return Button
	 */
	public function toButton(): Button{
		$button = new Button();
		$button->text = $button->title = $this->getTitle();
		$button->link = $this->getTitleLink();
		$attributes = $button->attributesToArray();
		foreach($attributes as $property => $value){
			if(property_exists($this, $property)){
				$button->$property = $this->$property;
			}
		}
		return $button;
	}
	public function setTitleLinkFromException(Throwable $e){
		$url = PHPStormExceptionButton::urlForException($e);
		$this->setTitleLink($url);
		return $this;
	}
	/**
	 * Set the title to use for the attachment
	 * @param string $title
	 * @return self
	 * DON'T DELETE THIS.  The return type is wrong in the package
	 * @noinspection PhpDocSignatureInspection
	 */
	public function setTitle($title){
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		/** @noinspection PhpVoidFunctionResultUsedInspection */
		return parent::setTitle($title);
	}
}
