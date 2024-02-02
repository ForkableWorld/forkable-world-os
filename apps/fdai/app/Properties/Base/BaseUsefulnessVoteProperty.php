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
class BaseUsefulnessVoteProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The opinion of the data owner on whether or not knowledge of this relationship is useful.
                        -1 corresponds to a down vote. 1 corresponds to an up vote. 0 corresponds to removal of a
                        previous vote.  null corresponds to never having voted before.';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::ACTIVITY;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::CORRELATION_CAUSALITY_VOTE;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'usefulness_vote';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $title = 'Usefulness Vote';
	public $type = self::TYPE_INTEGER;

}
