<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Properties\VariableCategory\SometimesEnumProperty;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseIsGoalProperty extends SometimesEnumProperty
{
	public $default = self::SOMETIMES;
	public $description = 'is_goal';
	public $fontAwesome = FontAwesome::ANALYSIS;
	public $image = ImageUrls::BUSINESS_STRATEGY_GOAL;
	public $importance = false;
	public $isOrderable = false;
	public $name = self::NAME;
	public const NAME = 'is_goal';
	public $showOnDetail = true;
	public $title = 'Is Goal';
}
