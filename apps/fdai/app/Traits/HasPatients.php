<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\DataSources\QMClient;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoEmailAddressException;
use App\Exceptions\UnauthorizedException;
use App\Mail\PatientInvitationEmail;
use App\Models\Application;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Properties\User\UserNumberOfPatientsProperty;
use App\Slim\Configuration\RouteConfiguration;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\User\QMUser;
use App\Utils\Env;
use App\Utils\IonicHelper;
use Illuminate\Support\Collection;
use SendGrid\Response;
trait HasPatients {
	public function canSeeOtherUsers(): bool{
		return $this->isAdmin() || $this->hasPatients();
	}
	public function getNumberOfPatients(): int{
		$l = $this->l();
		$number = $l->number_of_patients;
		if($number !== null){
			return $number;
		}
		$val = UserNumberOfPatientsProperty::calculate($this);
		try {
			$this->save();
		} catch (ModelValidationException $e) {
			le($e, $this);
		}
		return $val;
	}
	public function hasPatients(): bool{
		$val = $this->getNumberOfPatients();
		return $val > 0;
	}
	/**
	 * @return Collection|OAAccessToken[]
	 */
	public function getPatientAccessTokens():Collection{
		if($this->relationLoaded('patient_access_tokens')){
			return $this->getRelation('patient_access_tokens');
		}
        $qb = OAAccessToken::whereClientId($this->getPhysicianClientId())
            ->where(OAAccessToken::FIELD_USER_ID, "<>", $this->getUserId());
            //->groupBy([OAAccessToken::FIELD_USER_ID]);
        QMAccessToken::addExpirationWhereClause($qb);
        $sql = $qb->toSql();
        $tokens = $qb->get();
		$this->setRelation('patient_access_tokens', $tokens);
		return $tokens;
	}
	public function getPatientUserIds(): array{
		$patients = $this->getPatientAccessTokens();
		return $patients->pluck(OAAccessToken::FIELD_USER_ID)->all();
	}
	/**
	 * @return User[]
	 */
	public function getPatients(): array{
		$ids = $this->getAccessibleUserIds();
		$patients = [];
		foreach($ids as $id){
			$patients[] = User::findInMemoryOrDB($id);
		}
		return $patients;
	}
	public function getAccessibleUserIds(): array{
		if($this->hasPatients()){
			$ids = $this->getPatientUserIds();
		}
		$ids[] = $this->getId();
        if($secret = BaseClientSecretProperty::fromRequest()){
            $client = OAClient::fromRequest();
            if($client && $client->client_secret === $secret){
                $byClient = User::whereClientId($client->client_id)->pluck(User::FIELD_ID)->all();
                $ids = array_unique(array_merge($ids, $byClient));
            }
        }
		return $ids;
	}
	/**
	 * @return string
	 */
	public function getPatientAuthorizationUrl(): string{
		$participantUrl =
			Env::getAppUrl() . //getHostAppSettings()->additionalSettings->downloadLinks->webApp .
			'/oauth/authorize?response_type=token&scope=' . //'writemeasurements'.
			RouteConfiguration::SCOPE_READ_MEASUREMENTS . '&client_id=' . $this->getPhysicianClientId();
		return $participantUrl;
	}
	/**
	 * @return string
	 */
	public function getDataSharingInvitationEmailLink(): string{
		$participantUrl = $this->getPatientAuthorizationUrl();
		/** @noinspection SpellCheckingInspection */
		$invitationBody = 'Hi!%20%0A%0ADid%20you%20know%20that%20you%20can%20use%20' . config('app.name') .
			'%20(https%3A%2F%2Fquantimo.do)%20to%20easily%20track%20symptoms%20and%20treatments%2C%20import%20data%20from%20devices%20like%20Fitbit%2C%20and%20then%20see%20an%20analysis%20of%20your%20data%20showing%20the%20strongest%20predictors%20of%20your%20symptoms%3F%20%0A%0AYou%20can%20also%20opt-in%20share%20your%20data%20with%20me%20at%3A%0A' .
			urlencode($participantUrl) . '%0A%0AHave%20a%20great%20day!';
		$invitationSubject = rawurlencode('Invitation to Share Your Data');
		$link = 'mailto:?subject=' . $invitationSubject . '&body=' . $invitationBody;
		return $link;
	}
	/**
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException|UnauthorizedException
     */
	public function getOrCreatePhysicianApp(): Application{
		return $this->getOrCreateIndividualClientApp();
	}

    /**
     * @return OAClient
     * @throws UnauthorizedException
     */
	public function getPhysicianClient(): OAClient {
		$c = OAClient::findInMemoryOrDB($this->getPhysicianClientId());
		if(!$c){
			try {
				$c = $this->getOrCreatePhysicianApp()->getClient();
				return $c;
			} catch (InvalidEmailException|NoEmailAddressException $e) {
				le($e);
			}
		}
		return $c;
	}

    /**
     * @return Application
     * @throws InvalidEmailException
     * @throws NoEmailAddressException
     * @throws UnauthorizedException
     */
	public function getOrCreateIndividualClientApp(): Application {
		$clientId = $this->getPhysicianClientId();
        Application::whereClientId($clientId)
            ->update([Application::FIELD_DELETED_AT => null]);
		$app = Application::findByClientId($clientId);
		if(!$app){
			$app = $this->createPhysicianClient();
		}
		return $app;
	}

    /**
     * @return string
     * @throws InvalidEmailException
     * @throws NoEmailAddressException
     * @throws UnauthorizedException
     */
	public function getPhysicianClientSecret(): string{
		return $this->getOrCreateIndividualClientApp()->getClientSecret();
	}
	/**
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 */
	public function getClient(): OAClient{
		if($this->client){
			return $this->client;
		}
		$this->client = OAClient::findInMemoryOrDB($this->getPhysicianClientId());
		if($this->client){
			return $this->client;
		}
		return $this->client = $this->getOrCreatePhysicianApp()->getClient();
	}
	/**
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 */
	public function getPhysicianClientParams(): array{
		return [
			'quantimodo_client_id' => $this->getPhysicianClientId(),
			'quantimodo_client_secret' => $this->getPhysicianClientSecret(),
			'settings_url' => $this->getOrCreatePhysicianApp()->getBuilderUrl(),
		];
	}

    /**
     * @return Application
     * @throws InvalidEmailException
     * @throws NoEmailAddressException
     * @throws UnauthorizedException
     */
	private function createPhysicianClient(): Application {
		$clientId = $this->getPhysicianClientId();
		$redirect = QMAuth::getLoginRequestParam(OAClient::FIELD_REDIRECT_URI);
		if($redirect){
			$redirectUris[] = $redirect;
		}
		$redirectUris[] = IonicHelper::getIntroUrl([]);
		$client = OAClient::findInMemoryOrDB($clientId);
		if(!$client){
			$client = QMClient::createClient($clientId, $this->getQMUser(), $redirectUris);
		}
		$app = $client->createApplication([
			'app_display_name' => $this->getTitleAttribute(),
			'plan_id' => 0,
			'homepage_url' => "https://web.quantimo.do",
			'physician' => 1,
			'billing_enabled' => 0,
		]);
		$app->getOrCreateAccessAndRefreshTokenArrays(QMUser::demo()->getId(),
			RouteConfiguration::SCOPE_READ_MEASUREMENTS);
		return $app;
	}
	/**
	 * @return string
	 */
	public function getPhysicianClientId(): string{
		//return QMClient::sanitizeClientId($this->email); // Don't use email because they can never change it
		//return $this->getLoginName(); // Don't use login name because it consumes an id that could go to an app
		//return "user-".$this->getId(); // Let's not use this because it's not easily identifiable
		// Doesn't consume app client id and login name should not be allowed to change anyway for security reasons
		return BaseClientIdProperty::generatePhysicianClientId($this);
	}
	/**
	 * @param string $patientEmail
	 * @return Response
	 * @throws InvalidEmailException
	 * @throws \App\Mail\TooManyEmailsException
	 * @throws \SendGrid\Mail\TypeException
	 */
	public function sendInvitationToPatient(string $patientEmail): Response{
		$email = new PatientInvitationEmail($this->getUser()->getPhysicianUser(), $patientEmail);
		$email->send();
		$response = $email->getResponse();
		return $response;
	}
}
