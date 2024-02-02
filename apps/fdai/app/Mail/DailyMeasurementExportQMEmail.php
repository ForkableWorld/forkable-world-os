<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use SendGrid\Mail\TypeException;
class DailyMeasurementExportQMEmail extends QMSendgrid {
	/**
	 * @param int $recipientUserId
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 */
	public function __construct(int $recipientUserId){
		$this->setRecipientAndEmailFromUserId($recipientUserId);
		$filePath = QMMeasurement::exportAllDailyMeasurementsMatrixToCsv($recipientUserId);
		$u = $this->getRecipientUser();
		$url = $u->uploadFile($filePath, file_get_contents($filePath));
		$this->addAttachmentByFilePath($filePath, null, $url);
		parent::__construct($recipientUserId);
	}
	/**
	 * @return array|mixed
	 */
	public function getOrSetHtmlOrTemplateParams(){
		$body = [
			'emailType' => self::SENT_EMAIL_TYPE_MEASUREMENT_EXPORT,
			'headerText' => "Enjoy your data!",
		];
		return $body;
	}
	/**
	 * @return mixed|void
	 */
	public function getOrGenerateEmailSubject(): string{
		try {
			return $this->setSubject('Daily-Aggregated Measurement Export');
		} catch (TypeException $e) {
			/** @var \LogicException $e */
			throw $e;
		}
	}
	public static function getTestInstance(): QMSendgrid{
		return new DailyMeasurementExportQMEmail(UserIdProperty::USER_ID_MIKE);
	}
}
