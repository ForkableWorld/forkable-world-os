<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\BaseModel;
use App\Models\Unit;
class BaseOriginalUnitIdProperty extends BaseUnitIdProperty{
	public $description = 'Unit id of measurement as originally submitted';
	public $canBeChangedToNull = false;
	public $name = self::NAME;
	public const NAME = 'original_unit_id';
	public $title = 'Original Unit';
    public function shouldShowFilter():bool{return false;}
}
