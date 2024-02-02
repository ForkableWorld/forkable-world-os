<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Utils;
class IPHelper {
    const IP_MIKE = '24.216.165.165';
	/**
	 * @return string|null
	 */
	public static function getInternalIp(): ?string{
		if(isset($_SERVER['SERVER_ADDR'])){
			return $_SERVER['SERVER_ADDR'];
		}
		return null;
	}
	public static function getRemoteIP(): ?string{
		if(isset($_SERVER['REMOTE_ADDR'])){
			return $_SERVER['REMOTE_ADDR'];
		}
		return null;
	}
	/**
	 * @return string|null
	 */
	public static function getClientIp(): ?string{
		if(!empty($_SERVER['HTTP_CLIENT_IP'])){
			$IP = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$IP = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif(!empty($_SERVER['HTTP_X_FORWARDED'])){
			$IP = $_SERVER['HTTP_X_FORWARDED'];
		} elseif(!empty($_SERVER['HTTP_FORWARDED_FOR'])){
			$IP = $_SERVER['HTTP_FORWARDED_FOR'];
		} elseif(!empty($_SERVER['HTTP_FORWARDED'])){
			$IP = $_SERVER['HTTP_FORWARDED'];
		} elseif(!empty($_SERVER['REMOTE_ADDR'])){
			$IP = $_SERVER['REMOTE_ADDR'];
		} else{
			$IP = null;
		}
		if($IP === "127.0.0.1"){return null;}
		return $IP;
	}
	/**
	 * @param string $host
	 * @return bool
	 */
	public static function isIp(string $host): bool{
		if (filter_var($host, FILTER_VALIDATE_IP)) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * @param string $value
	 * @return bool
	 */
	public static function validIp(string $value): bool{
		return ip2long($value) !== false;
	}
}
