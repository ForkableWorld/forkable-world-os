<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Exceptions;
class NAInternalErrorType extends NAClientException {
    /**
     * NAInternalErrorType constructor.
     * @param $message
     */
    public function __construct($message){
        parent::__construct(0, $message, INTERNAL_ERROR_TYPE);
    }
}
?>
