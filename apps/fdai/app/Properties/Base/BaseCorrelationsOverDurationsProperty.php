<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsArray;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCorrelationsOverDurationsProperty extends BaseProperty{
	use IsArray;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'correlations_over_durations';
    public $example = array (
        86400 => 0.09138388802318811,
        172800 => 0.11441749692841008,
        345600 => 0.07571970052141365,
        691200 => 0.07881142724825704,
        1382400 => 0.07521914663169935,
        2764800 => 0.08580398627037786,
    );
	public $fieldType = 'array';
	public $fontAwesome = FontAwesome::AGGREGATE_CORRELATION;
	public $htmlInput = 'table';
	public $htmlType = 'table';
	public $image = ImageUrls::CORRELATION;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'correlations_over_durations';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::ARRAY;
	public $title = 'VariableRelationships Over Durations';
	public $type = PhpTypes::ARRAY;

}
