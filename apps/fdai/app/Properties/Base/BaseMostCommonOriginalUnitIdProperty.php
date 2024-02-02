<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\ForeignKeyIdTrait;
class BaseMostCommonOriginalUnitIdProperty extends BaseUnitIdProperty{
	use ForeignKeyIdTrait;
	public $description = 'Most common Unit';
	public $name = self::NAME;
	public const NAME = 'most_common_original_unit_id';
	public $title = 'Most Common Original Unit';
	public $canBeChangedToNull = true;
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return false;}
    public function shouldShowFilter():bool{return false;}
}
