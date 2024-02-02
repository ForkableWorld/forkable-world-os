<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
use App\UI\IonIcon;
use App\Variables\VariableSearchResult;
class StudyCreationStateButton extends IonicButton {
	public $accessibilityText = 'Create Study';
	public $action = '/#/app/study-creation';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'study-creation-state-button';
	public $image = 'https://static.quantimo.do/img/education/png/studying.png';
	public $ionIcon = IonIcon::study;
	public $link = '/#/app/study-creation';
	public $stateName = 'app.studyCreation';
	public $stateParams = [];
	public $text = 'Create Study';
	public $title = 'Create Study';
	public $tooltip = 'Create Study';
	public $menus = [];
	public function __construct(VariableSearchResult $v = null){
		parent::__construct();
		if($v){
			if($v->isOutcome()){
				$this->parameters['effectVariableName'] = $v->getNameAttribute();
			} else{
				$this->parameters['causeVariableName'] = $v->getNameAttribute();
			}
		}
	}
}
