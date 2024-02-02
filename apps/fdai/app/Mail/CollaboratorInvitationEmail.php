<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\AppSettings\AppSettings;
use App\Exceptions\ClientNotFoundException;
use App\Http\Requests\PasswordRequest;
use App\Models\Application;
use App\Slim\Model\User\QMUser;
class CollaboratorInvitationEmail extends DefaultEmail {
	/**
	 * @var AppSettings
	 */
	public $appSettings;
	public $blockBrownBodyText = "Need help? Just reply to this email!";
	public const MINIMUM_SECONDS_BETWEEN_EMAILS = 3600;
	/**
	 * @var string
	 */
	public $clientId;
	/**
	 * @var bool
	 */
	private $newUser;
	/**
	 * Create a new message instance.
	 * @param string $address
	 * @param string $clientId
	 * @throws TooManyEmailsException
	 */
	public function __construct(string $address, string $clientId){
		$this->clientId = $clientId;
		$collaborator = QMUser::findByEmail($address);
		if(!$collaborator){
			$this->newUser = true;
			$collaborator = QMUser::getOrCreateByEmail($address, $clientId);
		}
		$this->user = $collaborator->l();
		parent::__construct($address);
	}
	/**
	 * @return CollaboratorInvitationEmail|DefaultEmail
	 * @throws ClientNotFoundException
	 */
	public function build(){
		$appSettings = Application::getClientAppSettings($this->clientId);
		$ownerUser = $appSettings->getQmUser();
		$this->subject($ownerUser->displayName . " wants to share their $appSettings->appDisplayName app with you!");
		$this->from($ownerUser->email, $ownerUser->displayName);
		$imageLink = $appSettings->additionalSettings->downloadLinks->images->appDesigner;
		if($appSettings->additionalSettings->appImages->appIcon){
			$imageLink = $appSettings->additionalSettings->appImages->appIcon;
		}
		if($this->newUser){
			$msg = "Create a password <a href='" . PasswordRequest::getLink($this->recipientAddress)."'>here</a> to get started.";
		} else{
			$msg = "Just login with the account that has the email address $this->recipientAddress.";
		}
		$this->params = [
			'emailType' => QMSendgrid::SENT_EMAIL_TYPE_COLLABORATOR_INVITATION . '-' . $appSettings->clientId,
			// Add client id to avoid 1 email per hour issues
//			'blockBlue' => [
//				'titleText' => "Then you can build it for the web, iOS and Android.",
//				'bodyText' => $msg,
//				"button" => [
//					'text' => "Get Started",
//					'link' => Application::getAppDesignerLink($appSettings->clientId),
//				],
//				'image' => [
//					'imageUrl' => $imageLink,
//					'width' => '100',
//					'height' => "100",
//				],
//			],
//			'blockOrange' => [
//				'titleText' => "Already have your own existing app?",
//				'bodyText' => $appSettings->additionalSettings->downloadLinks->descriptions->integrationGuide,
//				"button" => [
//					'text' => "Integrate",
//					'link' => $appSettings->additionalSettings->downloadLinks->integrationGuide,
//				],
//				'image' => [
//					'imageUrl' => $appSettings->additionalSettings->downloadLinks->images->integrationGuide,
//					'width' => '100',
//					'height' => "100",
//				],
//			],
			'blockBrownBodyText' => "Need help? Just reply to this email!",
			'headerText' => "You've been added as a collaborator on $appSettings->appDisplayName!",
		];
		return $this->view('email.default-email', $this->getParams());
	}
}
