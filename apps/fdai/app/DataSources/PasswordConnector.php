<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\DataSources\ConnectParameters\UsernameConnectParameter;
use App\Exceptions\CredentialsNotFoundException;
use App\Slim\Controller\Connector\ConnectException;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Controller\Connector\ConnectorNonOauthConnectResponse;
use App\Types\QMArr;
abstract class PasswordConnector extends QMConnector
{
    public $mobileConnectMethod = 'input';
	/**
	 * @param array $params
	 * @return ConnectorNonOauthConnectResponse
	 * @throws ConnectException
	 */
    protected function connectWithUserNameAndPassword(array $params): ConnectorNonOauthConnectResponse{
        $user = QMArr::getValue($params, ['Username', 'user', 'email']);
        if(empty($user)){
            throw new ConnectException($this, 'No username specified', 400,
                                       'No username specified');
        }
        $pwd = QMArr::getValue($params, ['Password', 'pass']);
        if(empty($pwd)){
	        throw new ConnectException($this, 'No password specified', 400,
		        'No password specified');
        }
        $this->credentialsArray = $credentials = ['username' => $user, 'password' => $pwd];
        $this->storeCredentials($credentials);
        return new ConnectorNonOauthConnectResponse($this);
    }
	/**
	 * @param string $message
	 * @return ConnectorException
	 * @throws ConnectException
	 */
    protected function handleFailedLogin(string $message = 'Wrong username or password'): ConnectorException{
        $this->disconnectBecauseUnAuthorized($message);
        throw new ConnectException($this, $message, 403);
    }
    /**
     * @param string $text
     * @return ConnectInstructions
     */
    protected function getUserNamePasswordConnectInstructions(string $text = "Enter your credentials"): ConnectInstructions{
        $parameters = [
            new UsernameConnectParameter(),
            new ConnectParameter('Password', 'password', 'password')
        ];
        return $this->getNonOAuthConnectInstructions($parameters, $text);
    }
    /**
     * @return string
     */
    protected function getConnectorPassword(): string{
        return $this->getCredentialsArray('password');
    }
    /**
     * @throws CredentialsNotFoundException
     */
    public function validateCredentials(): void{
        if(!$this->getCredentialsArray()){
            throw new CredentialsNotFoundException($this);
        }
    }
	/**
	 * @param array $parameters
	 * @return ConnectorNonOauthConnectResponse
	 * @throws ConnectException
	 */
	public function connect($parameters): ConnectorNonOauthConnectResponse{
		return $this->connectWithUserNameAndPassword($parameters);
	}
	/**
	 * @return ConnectInstructions
	 */
	public function getConnectInstructions(): ?ConnectInstructions{
		return $this->getUserNamePasswordConnectInstructions();
	}
}
