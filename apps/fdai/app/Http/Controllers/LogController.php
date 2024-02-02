<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers;
class LogController extends Controller
{
    public function getNginxAccessLog(){
        echo file_get_contents('/var/log/nginx/access.log');
    }
    public function getNginxErrorLog(){
        echo file_get_contents('/var/log/nginx/error.log');
    }
    public function getPHPLog(){
        echo file_get_contents('/var/log/php7.4-fpm.log');
    }
}
