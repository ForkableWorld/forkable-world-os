<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCausalityVoteProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The opinion of the data owner on whether or not there is a plausible mechanism of action
                        by which the predictor variable could influence the outcome variable.';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::ACTIVITY;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::CORRELATION_CAUSALITY_VOTE;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'causality_vote';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $title = 'Causality Vote';
	public $type = self::TYPE_INTEGER;

}
