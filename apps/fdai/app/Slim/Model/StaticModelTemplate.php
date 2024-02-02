<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
class StaticModelTemplate {
	//__PROPERTIES_HERE__
	public function __construct($obj = null){
		if($obj){
			$this->populateFieldsByArrayOrObject($obj);
		}
	}
}
