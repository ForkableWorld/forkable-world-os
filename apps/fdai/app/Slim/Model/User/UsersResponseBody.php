<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\User;
use App\Buttons\QMButton;
use App\Cards\QMCard;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMResponseBody;
use App\UI\IonIcon;
class UsersResponseBody extends QMResponseBody {
	public $users;
	public $authUrl;
	public $description = "Users who have granted access to their data";
	public $link;
	public $card;
	/**
	 * UsersResponseBody constructor.
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 */
	public function __construct(){
		parent::__construct();
		$physician = QMAuth::getUser();
		$s = $physician->getOrCreatePhysicianApp();
		$tokens = $physician->getPatientAccessTokens();
		foreach($tokens as $token){
			$this->users[] = $token->getQMUser();
		}
		//$this->description = "Invite your patients to share their data by giving them this link ".$physician->getPatientAuthorizationUrl()." and then you'll be able to view their data at https://physician.quantimo.do";
		$this->description = $s->replaceAliases("Once a patient accepts your invitation, you can click name to switch to their account. Then you'll be able to
            see analytics such as the strongest predictors of their symptoms,
            add treatment and symptom rating reminders reminders,
            add new symptom ratings, treatments, and other measurements for them,
            import their digital health data from other apps and devices,
            and review their past symptoms, treatments, vitals, and other measurements.
            Once you're done, click the \"Back to my account\" link at the top of the page.");
		$this->link = $physician->getDataSharingInvitationEmailLink();
		$card = new QMCard('sharing-invitation-card');
		$card->setHeaderTitle($this->summary = $s->replaceAliases("Invite Your Patients"));
		$card->setContent($this->description);
		$button = new QMButton("Invite via Email", null, null, IonIcon::email);
		$button->setUrl($physician->getDataSharingInvitationEmailLink());
		$card->addButton($button);
		$button = new QMButton($s->replaceAliases("Copy Your Patient Authorization URL to Clipboard"), null, null,
			IonIcon::link);
		$button->setUrl($physician->getPatientAuthorizationUrl());
		$card->addButton($button);
		$card->setIonIcon(IonIcon::medkit);
		$card->setHtml();
		$this->card = $card;
		$this->authUrl = $physician->getPatientAuthorizationUrl();
	}
}
