<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Models\User;
use App\Studies\QMStudy;
use App\Traits\HasCauseAndEffect;
class StudyJoinEmail extends QMSendgrid {
	private $hasCauseAndEffect;
	/**
	 * @param User $recipientUser
	 * @param HasCauseAndEffect|QMStudy $hasCauseAndEffect
	 */
	public function __construct(User $recipientUser, $hasCauseAndEffect){
		$this->sourceObject = $this->hasCauseAndEffect = $hasCauseAndEffect;
		parent::__construct($recipientUser->id);
	}
	/**
	 * @return QMStudy|HasCauseAndEffect
	 */
	public function getHasCauseAndEffect(){
		return $this->hasCauseAndEffect;
	}
	/**
	 * @return string
	 */
	public function getOrGenerateEmailSubject(): string{
		$causeVariable = $this->getHasCauseAndEffect()->getOrSetCauseQMVariable();
		$effectVariable = $this->getHasCauseAndEffect()->getOrSetEffectQMVariable();
		$subject =
			"Thanks for joining our study to determine the effects of $causeVariable->name on $effectVariable->name!";
		$this->setSubject($subject);
		return $subject;
	}
	/**
	 * @return array
	 */
	public function getOrSetHtmlOrTemplateParams(): array{
		$sc = $this->getHasCauseAndEffect();
		$links = $sc->getStudyLinks();
		$cause = $sc->getOrSetCauseQMVariable();
		$effect = $sc->getOrSetEffectQMVariable();
		$causeConnector = $cause->getMostCommonAffiliatedDataSourceOrQuantiModo();
		$effectConnector = $effect->getMostCommonAffiliatedDataSourceOrQuantiModo();
		$body = [
			'headerText' => "You are science!",
			'blockBlue' => [
				'titleText' => "How to Track " . $cause->getOrSetVariableDisplayName(),
				'bodyText' => $cause->getTrackingInstructionsHtml(),
				'image' => [
					'imageUrl' => $cause->pngUrl,
					'width' => '100',
					'height' => '100',
					'linkTo' => $causeConnector->getItUrl,
				],
				"button" => [
					'text' => "Track " . $cause->name,
					'link' => $causeConnector->getItUrl,
				],
			],
			'blockOrange' => [
				'titleText' => "How to Track " . $effect->getOrSetVariableDisplayName(),
				'image' => [
					'imageUrl' => $effect->pngUrl,
					'width' => '100',
					'height' => '100',
					'linkTo' => $effectConnector->getItUrl,
				],
				'bodyText' => $effect->getTrackingInstructionsHtml(),
				"button" => [
					'text' => "Track " . $effect->name,
					'link' => $effectConnector->getItUrl,
				],
			],
			'blockBrownBodyText' => "Help accelerate this study by sharing it with your friends!",
			'facebookLink' => $links->getStudyLinkFacebook(),
			//'googleLink'         => $links->getStudyLinkGoogle(),
			'twitterLink' => $links->getStudyLinkTwitter(),
		];
		return $body;
	}
	/**
	 * @return QMSendgrid
	 */
	public static function getTestInstance(): QMSendgrid{
		return new static(User::mike(), User::mike()->getBestUserStudy());
	}
}
