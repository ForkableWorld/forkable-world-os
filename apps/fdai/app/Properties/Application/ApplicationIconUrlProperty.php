<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Application;
use App\Models\Application;
use App\Traits\PropertyTraits\ApplicationProperty;
use App\Properties\Base\BaseIconUrlProperty;
class ApplicationIconUrlProperty extends BaseIconUrlProperty
{
    use ApplicationProperty;
    public $table = Application::TABLE;
    public $parentClass = Application::class;
	public static function updateAll(){
		$apps = Application::all();
		foreach($apps as $app){
			$existing = $app->icon_url;
			$prop = $app->getPropertyModel(static::NAME);
			if(!$prop->isValid()){

			}
		}
		parent::updateAll();
	}
}
