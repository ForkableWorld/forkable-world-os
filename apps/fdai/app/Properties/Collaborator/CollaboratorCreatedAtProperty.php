<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Collaborator;
use App\Models\Collaborator;
use App\Traits\PropertyTraits\CollaboratorProperty;
use App\Properties\Base\BaseCreatedAtProperty;
class CollaboratorCreatedAtProperty extends BaseCreatedAtProperty
{
    use CollaboratorProperty;
    public $table = Collaborator::TABLE;
    public $parentClass = Collaborator::class;
}
