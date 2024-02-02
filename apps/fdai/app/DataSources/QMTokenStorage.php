<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection UnserializeExploitsInspection */
namespace App\DataSources;
use App\Exceptions\CredentialsNotFoundException;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\AbstractToken;
use OAuth\Common\Token\TokenInterface;
/** Stores a token implementing TokenInterface in QuantiModo's connect db
 * TokenInterface tokens are used with the OAuth library that ships with the framework
 * and shouldn't be used elsewhere
 */
class QMTokenStorage implements TokenStorageInterface {
    /**
     * @var QMConnector
     */
    private $connector;
    /**
     * QMTokenStorageInterface constructor.
     * @param QMConnector $connector
     */
    public function __construct(QMConnector $connector){
        $this->connector = $connector;
    }
    /** {@inheritDoc} */
    public function retrieveAccessToken($service = null): AbstractToken{
		$t = $this->hasAccessToken($service);
        if(!$t){
            throw new CredentialsNotFoundException($this->connector, "$service Token not found");
        }
        return $t;
    }
    /** {@inheritDoc} */
    public function storeAccessToken($service, TokenInterface $token){
        $endOfLife = $token->getEndOfLife();
        $this->connector->getCredentialStorageFromMemory()->store(['token' => serialize($token)], $endOfLife);
        return $this;
    }
    /**
     * @param null $service // Needed to match parent
     * @return AbstractToken
     */
    public function hasAccessToken($service = null): ?AbstractToken {
        $credentials = $this->connector->getCredentialStorageFromMemory()->get();
        if(isset($credentials['token'])){
	        if(is_string($credentials['token'])){
		        return unserialize($credentials['token']);
	        }
	        return $credentials['token'];
        }
	    if($credentials){le("credentials in wrong format: ", $credentials);}
        return null;
    }
    /** {@inheritDoc} */
    public function clearToken($service){
	    $this->connector->getCredentialStorageFromMemory()->hardDeleteCredentials(__METHOD__);
        return $this;
    }
    /** {@inheritDoc} */
    public function clearAllTokens(){
	    $this->connector->getCredentialStorageFromMemory()->hardDeleteCredentials(__METHOD__);
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function storeAuthorizationState($service, $state){
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function hasAuthorizationState($service): ?bool{
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function retrieveAuthorizationState($service): ?string{
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function clearAuthorizationState($service){
        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function clearAllAuthorizationStates(){
        return $this;
    }
}
