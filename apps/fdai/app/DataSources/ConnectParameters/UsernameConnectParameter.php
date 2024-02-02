<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\ConnectParameters;
use App\DataSources\ConnectParameter;
class UsernameConnectParameter extends ConnectParameter {
    public function __construct(){
        parent::__construct('Username', 'username', 'text');
    }
}
