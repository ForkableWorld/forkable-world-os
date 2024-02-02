<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\AppSettings\AppSettings;
use App\AppSettings\AppStatus\BuildStatus;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Models\Application;
use App\Models\SentEmail;
use App\Types\QMStr;
use App\UI\ImageUrls;
class AndroidBuildEmail extends DefaultEmail {
	/**
	 * @var AppSettings
	 */
	public $appSettings;
	public $blockBrownBodyText = "Need help? Just reply to this email!";
	public const MINIMUM_SECONDS_BETWEEN_EMAILS = 3600;
	/**
	 * Create a new message instance.
	 * @param string $address
	 * @param AppSettings $appSettings
	 * @throws TooManyEmailsException
	 */
	public function __construct(string $address, AppSettings $appSettings){
		$this->appSettings = $appSettings;
		parent::__construct($address);
		$this->blockBlue = [
			'titleText' => "Click to download and install!",
			'bodyText' => QMStr::getAppEditInstructionsHtml($appSettings->clientId),
			'image' => [
				'imageUrl' => ImageUrls::ESSENTIAL_COLLECTION_DOWNLOAD,
				'width' => '100',
				'height' => "100",
			],
			'buttons' => $this->getDownloadButtons($appSettings),
		];
		$this->subject("The $appSettings->appDisplayName Android app and Chrome extension are ready!");
	}
	/**
	 * @param AppSettings $applicationSettings
	 * @return array
	 * @throws BadRequestException
	 */
	private function getDownloadButtons($applicationSettings){
		$appStatus = $applicationSettings->getAppStatus();
		$buildStatus = $appStatus->getBuildStatus();
		$betaDownloadLinks = $appStatus->getBetaDownloadLinks();
		foreach($buildStatus as $key => $value){
			if($value !== BuildStatus::STATUS_BUILDING){
				$button = [
					'text' => QMStr::camelToTitle($key),
					'link' => $betaDownloadLinks->$key,
				];
				if($key === "chromeExtension"){
					$button['additionalText'] = QMStr::getChromeExtensionInstructions();
				}
				$downloadButtons[] = $button;
			}
		}
		if(!isset($downloadButtons)){
			throw new BadRequestException("All apps and extensions still have BUILDING status.  Please try again later");
		}
		return $downloadButtons;
	}
	/**
	 * @param string $clientId
	 * @return SentEmail[]
	 * @throws ClientNotFoundException
	 * @throws TooManyEmailsException
	 */
	public static function sendAndroidBuildNotificationEmail(string $clientId): array{
		$app = Application::getClientAppSettings($clientId);
		$collaborators = $app->getCollaboratorUsers();
		$responses = [];
		foreach($collaborators as $c){
			try {
				$mail = new AndroidBuildEmail($c->getEmail(), $app);
				$responses[] = $mail->sendMe();
			} catch (InvalidEmailException | NoEmailAddressException $e) {
				$c->logError(__METHOD__.": ".$e->getMessage());
				continue;
			}
		}
		return $responses;
	}
}
