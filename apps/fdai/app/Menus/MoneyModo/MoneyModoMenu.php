<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\MoneyModo;
use App\Buttons\MoneyModo\BestStrategyButton;
use App\Buttons\MoneyModo\EconomicDataButton;
use App\Buttons\MoneyModo\StockButton;
use App\Buttons\MoneyModo\StrategyComparisonButton;
use App\Menus\QMMenu;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
class MoneyModoMenu extends QMMenu {
	public $badge;
	public $expanded = false;
	public $fontAwesome = FontAwesome::MONEY_BILL_ALT;
	public $image = ImageUrls::MONEY_MONEY_NOTES;
	public $ionIcon = IonIcon::money;
	public $rememberMenuState = true;
	public $title = "MoneyModo";
	public $tooltip = "Algorithmic trading strategy back testing.";
	const VARIABLE_TQQQ_DAILY_RETURN = "TQQQ Daily Return";
	public function getTitleAttribute(): string{ return $this->title; }
	public function getImage(): string{ return $this->image; }
	public function getFontAwesome(): string{ return $this->fontAwesome; }
	public function getTooltip(): string{ return $this->tooltip; }
	public function getButtons(): array{
		$arr = [];
		$arr[] = new StrategyComparisonButton();
		$arr[] = new BestStrategyButton();
		$arr[] = new EconomicDataButton();
		$arr[] = new StockButton();
		$this->addButtons($arr);
		return $this->buttons;
	}
}
