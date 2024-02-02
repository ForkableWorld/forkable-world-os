<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Properties\Base;
use App\Traits\ForeignKeyIdTrait;
class BaseLastUnitIdProperty extends BaseUnitIdProperty{
	use ForeignKeyIdTrait;
	public $description = 'ID of last Unit';
	public $name = self::NAME;
	public const NAME = 'last_unit_id';
	public $canBeChangedToNull = true;
    public function shouldShowFilter():bool{return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnDetail(): bool {return false;}
}
