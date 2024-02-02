<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\MoneyModo;
use App\Buttons\MoneyModo\StrategyComparisonButton;
use App\Menus\QMMenu;
use App\Pages\StrategyComparisonPage;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class StrategyComparisonMenu extends QMMenu {
	/**
	 * @var StrategyComparisonPage
	 */
	private $page;
	public function getTitleAttribute(): string{ return "Strategy Comparison"; }
	public function getImage(): string{ return ImageUrls::BUSINESS_STRATEGY_TROPHY; }
	public function getFontAwesome(): string{ return FontAwesome::CHARTS; }
	public function getTooltip(): string{ return "Comparison of the back tested returns from each strategy."; }
	public function __construct(StrategyComparisonPage $page){
		$this->page = $page;
	}
	/**
	 * @inheritDoc
	 */
	public function getButtons(): array{
		$buttons = [];
		$page = $this->getPage();
		$all = $page->getStrategies();
		foreach($all as $strategy){
			$range = $strategy->getTimeRangeString();
			if(!isset($buttons[$range])){
				$buttons[$range] = new StrategyComparisonButton($strategy);
			}
		}
		ksort($all);
		$this->addButtons($buttons);
		return $this->buttons;
	}
	/**
	 * @return StrategyComparisonPage
	 */
	public function getPage(): StrategyComparisonPage{
		return $this->page;
	}
}
