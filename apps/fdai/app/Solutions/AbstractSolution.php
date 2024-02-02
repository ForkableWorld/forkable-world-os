<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Buttons\QMButton;
use App\Exceptions\ExceptionHandler;
abstract class AbstractSolution {
	public array $documentationLinks = [];
	public function __toString(){
		return $this->render();
	}
	public function render(): string{
		return ExceptionHandler::renderSolution($this);
	}
	public function getSolutionButtons(){
		$buttons = QMButton::linksToButtons($this->getDocumentationLinks());
		return $buttons;
	}
	public function getDocumentationLinks(): array{
		return $this->documentationLinks;
	}
	/**
	 * @param array $documentationLinks
	 */
	public function setDocumentationLinks(array $documentationLinks): void{
		$this->documentationLinks = $documentationLinks;
	}
}
