<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\ForeignKeyIdTrait;
class BaseLastOriginalUnitIdProperty extends BaseUnitIdProperty{
	use ForeignKeyIdTrait;
	public $description = 'ID of last original Unit';
	public $name = self::NAME;
	public const NAME = 'last_original_unit_id';
	public $title = 'Last Original Unit';
	public $canBeChangedToNull = true;
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return false;}
    public function shouldShowFilter():bool{return false;}
}
