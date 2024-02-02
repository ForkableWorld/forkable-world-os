<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CommonTag;
use App\Models\CommonTag;
use App\Traits\PropertyTraits\CommonTagProperty;
use App\Properties\Base\BaseNumberOfDataPointsProperty;
class CommonTagNumberOfDataPointsProperty extends BaseNumberOfDataPointsProperty
{
    use CommonTagProperty;
    public $table = CommonTag::TABLE;
    public $parentClass = CommonTag::class;
}
