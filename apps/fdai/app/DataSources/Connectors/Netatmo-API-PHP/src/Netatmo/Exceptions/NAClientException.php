<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Exceptions;
define('CURL_ERROR_TYPE', 0);
define('API_ERROR_TYPE', 1);//error return from api
define('INTERNAL_ERROR_TYPE', 2); //error because internal state is not consistent
define('JSON_ERROR_TYPE', 3);
define('NOT_LOGGED_ERROR_TYPE', 4); //unable to get access token
/** OAuth2.0 Netatmo exception handling
 * @author Originally written by Thomas Rosenblatt <thomas.rosenblatt@netatmo.com>.
 */
class NAClientException extends NASDKException {
    public $error_type;
    /**
     * Make a new API Exception with the given result.
     * @param $code
     * @param $message
     * @param $error_type
     */
    public function __construct($code, $message, $error_type){
        $this->error_type = $error_type;
        parent::__construct($code, $message);
    }
}
?>
