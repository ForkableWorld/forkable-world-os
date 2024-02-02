<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

// Starting Response Stream
ob_start();
// printer
function pr($data){
	echo "<pre>\r\n";
	echo \App\Logging\QMLog::print_r($data, true);
	echo "<pre>\r\n";
}
function getCommand(): string{
	$root = __DIR__;
	$command = getParam('command');
	if(!$command){
		$script = getParam('script');
		$script = "prioritizer";
		if($script){
			$script = "$root/scripts/$script.ps1";
			if(!file_exists($script)){
				throw new Exception("$script not found!");
			}
			$command = "-File $script";
		}
	}
	return $command;
}
/**
 * @return mixed|null
 */
function getParam(string $name){
	return $_POST[$name] ?? $_GET[$name] ?? null;
}
// Check Request From Local
function fromLocal(): bool{
	if(php_sapi_name() === "cli"){
		return true;
	}
	if(isset($_SERVER["SERVER_NAME"]) && $_SERVER['SERVER_NAME'] === "localhost"){
		return true;
	}
	if(!isset($_SERVER['LOCAL_ADDR'])) return $_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR'];
	if(!isset($_SERVER['SERVER_ADDR'])) return $_SERVER['LOCAL_ADDR'] == $_SERVER['REMOTE_ADDR'];
	return ($_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR'] || $_SERVER['LOCAL_ADDR'] == $_SERVER['REMOTE_ADDR']);
}
// Get ClientIP Address
function clientIp(){
	return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
}
// Check Request From Trusted Zone
function isTrustedUser(): bool{
	if(fromLocal() === true) return true;
	return strpos(clientIp(), ALLOW_IP) === 0;
}
// Exception Renderer
function error_handler($num, $str, $file, $line, $context = null){
	$errArr = get_defined_vars(); // <--- create the $params array
	header('Content-Type: application/json');
	$errArr['success'] = false;
	$errArr['message'] = $num . " " . $str . ($line ? ' at line ' . $line : '');
	ob_clean();
	echo json_encode($errArr);
	exit();
}
// Exception Renderer
function exception_handler($e){
	error_handler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
}
set_error_handler("error_handler");
set_exception_handler("exception_handler");
