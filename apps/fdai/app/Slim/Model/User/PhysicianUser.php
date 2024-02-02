<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\User;
use App\AppSettings\PhysicianApplication;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Mail\DescriptiveOverviewForAllPatientsEmail;
use App\Mail\PatientDescriptiveOverviewEmail;
use App\Mail\PatientRootCauseUpdateEmail;
use App\Mail\TooManyEmailsException;
use App\Models\Application;
use App\Slim\Model\DBModel;
use App\Slim\Model\Reminders\AnonymousReminder;
use SendGrid\Mail\TypeException;

class PhysicianUser extends QMUser {
	/**
	 * @return PhysicianUser[]
	 */
	public static function getAll(): array{
		$apps = PhysicianApplication::getAll();
		$physicians = [];
		foreach($apps as $app){
			$user = $app->getQmUser();
			$physician = self::instantiateIfNecessary($user);
			$appClient = $app->getClientId();
			$physicianClientId = $physician->getPhysicianClientId();
			if($appClient === $physicianClientId){
				$physician->setPhysicianClientApplication($app);
			} else{
				$app->logInfo("$appClient is not a valid physician client id and should be $physicianClientId");
				//$physician->getOrCreateIndividualClientApp();
				//$app->hardDelete();
				continue;
			}
			$physicians[] = $physician;
		}
		return $physicians;
	}
	/**
	 * @return array
	 * @throws TooManyEmailsException
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 * @throws TypeException
	 */
	public function sendRootCauseReports(): array{
		$patients = $this->getPatients();
		$emails = [];
		foreach($patients as $patient){
			$email = new PatientRootCauseUpdateEmail($patient->getUser(), $this);
			$email->send();
			$emails[] = $email;
		}
		return $emails;
	}
	/**
	 * @return array
	 */
	public function sendDescriptiveOverviewReports(): array{
		$patients = $this->getPatients();
		$mails = [];
		foreach($patients as $patient){
			try {
				$email = new PatientDescriptiveOverviewEmail($patient->getUser(), $this);
				$email->send();
				$mails[] = $email;
			} catch (InvalidEmailException|TooManyEmailsException|NoEmailAddressException|TypeException $e) {
				ExceptionHandler::dumpOrNotify($e);
				continue;
			}
		}
		return $mails;
	}
	/**
	 * @return DescriptiveOverviewForAllPatientsEmail
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 * @throws TooManyEmailsException
	 * @throws TypeException
	 */
	public function sendDescriptiveOverviewForAllPatientsEmail(): ?DescriptiveOverviewForAllPatientsEmail{
		$patients = $this->getPatients();
		if(!$patients){
			$this->logInfo("No patients to create report for!");
			return null;
		}
		$email = new DescriptiveOverviewForAllPatientsEmail($this);
		$email->send();
		return $email;
	}
	/**
	 * @return Application
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 */
	public function getApp(): Application{
		return $this->getOrCreateIndividualClientApp();
	}
	/**
	 * @return AnonymousReminder[]
	 */
	public function getDefaultTrackingReminders(): array{
		$app = $this->getApp();
		return $app->getReminders();
	}
	/**
	 * @param $id
	 * @return PhysicianUser
	 */
	public static function find($id): ?DBModel{
		$user = QMUser::find($id);
		$physician = $user->getPhysicianUser();
		return $physician;
	}
}
