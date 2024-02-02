<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Buttons\Links\HelpButton;
use Facade\IgnitionContracts\Solution;
class GetHelpSolution extends AbstractSolution implements Solution {
	public $title;
	public $description;
	public function getSolutionTitle(): string{
		return $this->title ?? HelpButton::make()->getTitleAttribute();
	}
	public function getSolutionDescription(): string{
		return $this->description ??  HelpButton::make()->getTooltip();
	}
	public function getDocumentationLinks(): array{
		return ["Contact Us" => HelpButton::make()->getUrl()];
	}
}
