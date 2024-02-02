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
class BaseCorrelationsOverDelaysProperty extends BaseProperty{
	use IsArray;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $example = array (
        -2764800 => 0.043215682236285635,
        -1382400 => -0.00011747692145429497,
        -691200 => 0.12368496912904867,
        -345600 => 0.12596296571407928,
        -172800 => 0.08770366007880859,
        -86400 => 0.11473963188457176,
        0 => 0.09138388802318811,
        86400 => 0.10691279485795759,
        172800 => -0.09134741456742354,
        345600 => 0.0646505851853315,
        691200 => 0.05589971997434494,
        1382400 => 0.06089814390281907,
        2764800 => -0.058009667849809037,
    );
	public $description = 'correlations_over_delays';
	public $fieldType = 'array';
	public $fontAwesome = FontAwesome::CORRELATIONS;
	public $htmlInput = 'table';
	public $htmlType = 'table';
	public $image = ImageUrls::CORRELATIONS;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'correlations_over_delays';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::ARRAY;
	public $title = 'VariableRelationships Over Delays';
	public $type = PhpTypes::ARRAY;

}
