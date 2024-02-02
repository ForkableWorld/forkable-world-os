<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Collaborator;
use App\Models\Collaborator;
use App\Properties\Base\BaseTypeProperty;
use App\Traits\PropertyTraits\CollaboratorProperty;
class CollaboratorTypeProperty extends BaseTypeProperty
{
    use CollaboratorProperty;
	public const TYPE_COLLABORATOR = "collaborator";
	public const TYPE_OWNER        = "owner";
	public $table = Collaborator::TABLE;
    public $parentClass = Collaborator::class;
}
