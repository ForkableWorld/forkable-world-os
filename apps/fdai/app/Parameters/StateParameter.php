<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Parameters;
use App\DataSources\QMClient;
use App\Exceptions\UnauthorizedException;
use App\Http\Urls\FinalCallbackUrl;
use App\Http\Urls\IntendedUrl;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Types\QMArr;
use App\Types\QMStr;
class StateParameter extends QMParameter
{
	/**
	 * @var void
	 */
	private static $alreadyComplained = [];
	public $name = 'state';
    /**
     * @return array|int
     */
    public static function getDecodedStateParamArrayFromRequest(): ?array{
        $encoded = QMRequest::fromInput('state');
        if($encoded){
            $decoded = QMStr::base64_url_decode_array($encoded);
            if(empty($decoded)){
                $decoded = json_decode($encoded, true);
            }
            if($decoded !== null && !is_array($decoded)){
                QMLog::error("State parameter is ".\App\Logging\QMLog::print_r($decoded, true));
                return null;
            }
            if(!$decoded && !in_array($encoded, self::$alreadyComplained)){ // Causes infinite loop if we use
				// QMLog::error
	            self::$alreadyComplained[] = $encoded;
            	ConsoleLog::error("Couldn't decode this state param: ".\App\Logging\QMLog::print_r($encoded, true));
            }
            return $decoded;
        }
        return null;
    }
    /**
     * @return string
     */
    public static function getEncodedStateParam(): string{
        $stateArray = [];
        try {
            if(QMAuth::getQMUser()){ // Can't use connector user because we use user 1 as a dummy sometimes
                $stateArray[QMClient::FIELD_USER_ID] = QMAuth::getQMUser()->id;
            }
        } catch (UnauthorizedException $e) {
        }
        if($id = BaseClientIdProperty::fromRequest(false)){
            $stateArray[QMClient::FIELD_CLIENT_ID] = $id;
        }
        if($secret = BaseClientSecretProperty::fromRequest()){
            $stateArray[QMClient::FIELD_CLIENT_SECRET] = $secret;
        }
        if($url = IntendedUrl::get()){
            $stateArray[IntendedUrl::INTENDED_URL] = $url;
        }
	    if($url = FinalCallbackUrl::getIfSet()){
		    $stateArray[FinalCallbackUrl::NAME] = $url;
	    }
        return QMStr::base64_url_encode_array($stateArray);
    }
    /**
     * @param string $name
     * @param null $connectorName
     * @return mixed|null
     */
    public static function getValueFromStateParam(string $name, $connectorName = null){
        $uri = $_SERVER['REQUEST_URI'] ?? null;
        if(!$uri){
            return null;
        }
        if($connectorName && stripos($uri, $connectorName) === false){
            return null;
        }
        $decoded = StateParameter::getDecodedStateParamArrayFromRequest();
        if(!$decoded){
            return null;
        }
        return QMArr::getValueForSnakeOrCamelCaseKey($decoded, $name);
    }
    /**
     * @return string
     */
    public static function getUserIdFromStateParam(): ?string{
        return StateParameter::getValueFromStateParam(QMClient::FIELD_USER_ID);
    }
}
