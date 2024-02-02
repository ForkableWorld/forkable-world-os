<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Exceptions;
class NACurlErrorType extends NAClientException {
    /**
     * NACurlErrorType constructor.
     * @param $code
     * @param $message
     */
    public function __construct($code, $message){
        parent::__construct($code, $message, CURL_ERROR_TYPE);
    }
}
?>
