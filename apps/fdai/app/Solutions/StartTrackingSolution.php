<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Buttons\States\OnboardingStateButton;
use Facade\IgnitionContracts\Solution;
class StartTrackingSolution extends AbstractSolution implements Solution {
	/**
	 * @var string
	 */
	private $description;
	public function __construct(string $description = null){
		$this->description = $description;
	}
	public function getSolutionTitle(): string{
		return OnboardingStateButton::make()->getTitleAttribute();
	}
	public function getSolutionDescription(): string{
		return $this->description ?? OnboardingStateButton::make()->getTooltip();
	}
	public function getDocumentationLinks(): array{
		return ["Contact Us" => OnboardingStateButton::make()->getUrl()];
	}
}
