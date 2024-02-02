<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Slim\Model\Measurement\MeasurementExportRequest;
class MeasurementExportEmail extends QMSendgrid {
	public $measurementExportRequest;
	public $subject = "Here's your data!";
	/**
	 * @param MeasurementExportRequest $req
	 * @param string $path
	 */
	public function __construct(MeasurementExportRequest $req, string $path){
		$this->sourceObject = $req;
			//$url = $req->getQMUser()->uploadFile($path, file_get_contents($path));
		$this->addAttachmentByFilePath($path, $path);
		$this->measurementExportRequest = $req;
		parent::__construct($req->getUserId());
	}
	/**
	 * @return array|mixed
	 */
	public function getOrSetHtmlOrTemplateParams(){
		$body = [
			'emailType' => self::SENT_EMAIL_TYPE_MEASUREMENT_EXPORT,
			'headerText' => "Your measurements are in the attached spreadsheet. ",
		];
		return $body;
	}
	/**
	 * @return mixed|void
	 */
	public function getOrGenerateEmailSubject(): string{
		$s = 'Measurement Export';
		$this->setSubject($s);
		return $s;
	}
	/**
	 * @return QMSendgrid
	 */
	public static function getTestInstance(): QMSendgrid{
		return new static(new MeasurementExportRequest([]), 'tmp');
	}
}
