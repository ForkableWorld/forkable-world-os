<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Properties\Base\BaseIsPublicProperty;
use App\Properties\BaseProperty;
class SetPublicAction extends ChangeAttributeAction {
	public function getProperty(): BaseProperty{ return new BaseIsPublicProperty(); }
}
