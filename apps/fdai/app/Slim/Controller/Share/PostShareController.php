<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Share;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Exceptions\UnauthorizedException;
use App\Mail\PhysicianInvitationEmail;
use App\Mail\TooManyEmailsException;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\User\QMUser;
use SendGrid\Mail\TypeException;

class PostShareController extends PostController {
    /**
     * @throws InvalidEmailException
     * @throws NoEmailAddressException
     * @throws TooManyEmailsException
     * @throws TypeException
     * @throws UnauthorizedException
     */
	public function post(){
		$response = $this->shareWithIndividual();
		return $this->writeJsonWithGlobalFields(201, $response);
	}

    /**
     * @return array
     * @throws NoEmailAddressException
     * @throws InvalidEmailException
     * @throws UnauthorizedException
     * @throws TooManyEmailsException
     * @throws TypeException
     */
	public function shareWithIndividual(): array{

		$clientEmail = $this->getClientEmail();
		$scopes = $this->getScopes();
        try {
            $doctor = QMUser::getOrCreateByEmail($clientEmail);
        } catch (\Throwable $e) {
            $clientEmail = $this->getClientEmail();
            $scopes = $this->getScopes();
            le($e);
        }
		$application = $doctor->getOrCreateIndividualClientApp();
		$client = $application->getOrCreateClient();
		$patient = QMAuth::getQMUser();
		$accessToken = $patient->getOrCreateAccessAndRefreshToken($application->client_id, $scopes);
		$mail = new PhysicianInvitationEmail($doctor->getPhysicianUser(), $patient->getUser());
		$mail->send();
		$response['emailInvitation'] = $mail->summaryResponse;
		$response['authorizedClients'] = $patient->getAuthorizedClients();
		return $response;
	}
	/**
	 * @return array|mixed|null|string
	 */
	public function getScopes(){
		$scopes = $this->getBodyOrQueryParam('scopes');
		if(!$scopes){
			$scopes = QMAccessToken::SCOPE_READ_MEASUREMENTS;
		}
		return $scopes;
	}
}
