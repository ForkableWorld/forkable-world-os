<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Studies\QMStudy;
use App\Traits\HasCauseAndEffect;
class StudyCard extends QMCard {
	protected $hasCauseAndEffect;
	/**
	 * @param HasCauseAndEffect|\App\Traits\HasCorrelationCoefficient $hasCauseAndEffect
	 */
	public function __construct($hasCauseAndEffect){
		$this->hasCauseAndEffect = $hasCauseAndEffect;
		$this->setHtmlContent($hasCauseAndEffect->getTitleGaugesTagLineHeader(true, true));
		$links = $hasCauseAndEffect->getStudyLinks();
		$this->setUrl($links->getStudyLinkStatic());
		$this->setStudyButtons();
		$this->getParameters();
		$cause = $hasCauseAndEffect->getCauseVariable();
		parent::__construct($hasCauseAndEffect->getId());
		$this->setIonIcon($hasCauseAndEffect->getIonIcon());
		$this->setImage($hasCauseAndEffect->getImage());
		$this->sharingTitle = $hasCauseAndEffect->getSharingTitle();
		$this->sharingBody = $hasCauseAndEffect->getSharingDescription();
		$this->content = $hasCauseAndEffect->getTagLine();
		$this->title = $hasCauseAndEffect->getStudyTitle();
		$this->type = QMCard::TYPE_study;
	}
	public function setStudyButtons(){
		$study = $this->getHasCauseAndEffect();
		$this->sharingButtons = $study->getStudySharing()->getSharingButtons(false);
		$this->buttons = $study->getButtons();
		$actions = $study->getCardActionSheetButtons();
		$this->setActionSheetButtons($actions);
	}
	/**
	 * @return QMStudy|HasCauseAndEffect
	 */
	public function getHasCauseAndEffect(){
		return $this->hasCauseAndEffect;
	}
	/**
	 * @return array
	 */
	public function getParameters(): array{
		$study = $this->getHasCauseAndEffect();
		$this->addParameter('causeVariableName', $study->getCauseVariableName());
		$this->addParameter('causeVariableId', $study->getCauseVariableId());
		$this->addParameter('effectVariableName', $study->getEffectVariableName());
		$this->addParameter('effectVariableId', $study->getEffectVariableId());
		$this->addParameter('studyId', $study->getId());
		$this->addParameter('studyType', $study->getStudyType());
		return $this->parameters;
	}
}
