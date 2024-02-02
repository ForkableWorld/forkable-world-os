<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus;
use App\Buttons\DemoExamples\GradeReportExampleButton;
use App\Buttons\DemoExamples\RootCauseAnalysisReportExampleButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class ExamplesMenu extends QMMenu {
	/**
	 * @inheritDoc
	 */
	public function getButtons(): array{
		$buttons = [];
		$buttons[] = new GradeReportExampleButton();
		$buttons[] = new RootCauseAnalysisReportExampleButton();
		$this->addButtons($buttons);
		return $this->buttons;
	}
	public function getTitleAttribute(): string{
		return "Design Examples";
	}
	public function getImage(): string{
		return ImageUrls::DESIGN_TOOL_COLLECTION_IMAGE;
	}
	public function getFontAwesome(): string{
		return FontAwesome::DEVIANTART;
	}
	public function getTooltip(): string{
		return "Web design inspirational pieces";
	}
}
