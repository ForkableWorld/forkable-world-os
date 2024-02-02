<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Collaborator;
use App\Models\Collaborator;
use App\Properties\Base\BaseIntegerIdProperty;
use App\Traits\ForeignKeyIdTrait;
use App\Traits\PropertyTraits\CollaboratorProperty;
class CollaboratorIdProperty extends BaseIntegerIdProperty
{
    use ForeignKeyIdTrait;
    use CollaboratorProperty;
    public $table = Collaborator::TABLE;
    public $parentClass = Collaborator::class;
    public static function getForeignClass(): string{return Collaborator::class;}
}
