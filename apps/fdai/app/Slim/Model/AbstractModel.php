<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
/** Class AbstractModel
 * extract() and hydrate() methods are inspired by Zend Hydrator implementation
 * http://framework.zend.com/manual/2.3/en/modules/zend.stdlib.hydrator.html
 * @package App\Slim\Model
 */
class AbstractModel {
	/**
	 * Hydrate an object by populating setter methods
	 * @param array $data
	 */
	public function hydrate(array $data){
		foreach($data as $property => $value){
			$method = 'set' . ucfirst($property);
			if(is_callable([
				$this,
				$method,
			])){
				$this->$method($value);
			}
		}
	}
}
