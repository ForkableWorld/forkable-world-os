<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Exceptions;
/** Exception thrown by Netatmo SDK
 */
class NASDKException extends \Exception {
    /**
     * NASDKException constructor.
     * @param $code
     * @param $message
     */
    public function __construct($code, $message){
        parent::__construct($message, $code);
    }
}
?>
