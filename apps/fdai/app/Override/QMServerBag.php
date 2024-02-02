<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Override;
use App\Logging\QMLog;
class QMServerBag extends  \Symfony\Component\HttpFoundation\ServerBag
{
    /**
     * @param array $array
     * @return static
     */
    public static function __set_state(array $array) : self
    {
        $object = new self;
        foreach ($array as $key => $value) {
            $object->{$key} = $value;
        }
        return $object;
    }
	public static function populate(array $server){
		foreach($server as $key => $value){
			if(is_array($value)){
				$value = $value[0];
			}
			if(stripos($key, "REQUEST") === false &&
				stripos($key, "HTTP") === false &&
				!in_array($key, ['SERVER_NAME', "REMOTE_ADDR"])){
				continue;
			}
			$existing = $_SERVER[$key] ?? null;
			if(!empty($value) && $value !== $existing){
				if(!empty($existing) && !in_array($key, [
						'REQUEST_TIME_FLOAT',
						'REQUEST_TIME',
						'HTTP_ACCEPT_LANGUAGE',
						'HTTP_ACCEPT'
					])){
					QMLog::error("Changing \$_SERVER[$key] from $existing to " . $value . '...');
				}
				$_SERVER[$key] = $value;
			}
		}
	}
}
