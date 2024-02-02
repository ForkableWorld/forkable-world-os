<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Buttons;
use App\AppSettings\AppDesign\FeaturesListSettings;
class FeatureButton extends QMButton {
	public $premium;
	public static function generateButtons(){
		$arr = FeaturesListSettings::getDefaultFeaturesList();
		foreach($arr as $featureArr){
			$b = new FeatureButton();
			foreach($featureArr as $key => $value){
				$b->$key = $value;
			}
			$b->saveHardCodedModel();
		}
	}
}
