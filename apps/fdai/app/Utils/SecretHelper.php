<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\DataSources\Connectors\GithubConnector;
use App\DataSources\QMConnector;
use App\Exceptions\SecretException;
use App\Files\Env\EnvFile;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Slim\Model\Auth\QMAccessToken;
use App\Storage\Memory;
use App\Types\QMStr;
class SecretHelper {
	const OBFUSCATED = '[OBFUSCATED]';
	public const SECRET_FILE_PATTERNS = [
		".idea/",
		// Why is this secret? ".less",
		// Why is this secret? ".scss",
		'.cfg',
		'config',
		'credentials',
		'.crt',
		'.env',
		'wp-config',
		'.git',
		'private',
	];
	/**
	 * @var array
	 */
	private static $secretValues;
	/**
	 * @return string[]
	 */
	public static function getSecretNamePatterns(): array{
		return [
			'secret',
			'token',
			'password',
			//'connection',
			'DATABASE_URL',
			//'key',
			'_pass',
			'access_key',
			'api_key',
		];
	}
	public static function containsSecretyString(string $string): bool{
		foreach(self::getSecretNamePatterns() as $pattern){
			$pattern = strtolower($pattern);
			$string = strtolower($string);
			if(str_contains($string, $pattern)){
				return true;
			}
		}
		return false;
	}
	public static function getSecretValues(): array{
		if($values = self::$secretValues){
			if($t = Memory::getQmAccessTokenString()){
				$values['Memory::getQmAccessTokenString'] = $t;
			} // This changes
			return self::$secretValues = $values;
		}
		$secretValues = [];
		foreach(self::getSecretNamePatterns() as $pattern){
			$secretValues = array_merge($secretValues, Env::getNonEmptyEnvValuesWithNameLike($pattern));
		}
		$secretValues = array_merge($secretValues, QMConnector::getClientSecrets());
		unset($secretValues[Env::STORAGE_ACCESS_KEY_ID]); // STORAGE_ACCESS_KEY_ID is in referral urls
		unset($secretValues['CONNECTOR_FOURSQUARE_CLIENT_SECRET']); // CONNECTOR_FOURSQUARE_CLIENT_SECRET needed to look up location on clients
		if(count($_ENV) > 10){
			//self::validateSecretValues($secretValues);
			self::$secretValues = $secretValues;
		}
		$names = array_keys($secretValues);
		return $secretValues;
	}
	public static function isSecretyName(string $string): ?string{
		return self::findSecretNamePattern($string);
	}
	/**
	 * @param string $string
	 * @return string|null
	 */
	public static function findSecretNamePattern(string $string): ?string{
		$whiteList = [
			'Stack Trace',
			'Token not found',
			'hardDelete',
			'DeviceToken',
			BaseAccessTokenProperty::TEST_ACCESS_TOKEN_FOR_ANY_REFERRER_DOMAIN,
			'sessionTokenObject":null',
		];
		foreach($whiteList as $w){
			$string = str_replace($w, "", $string);
		}
		foreach(self::getSecretNamePatterns() as $secretNamePattern){
			if(stripos($string, $secretNamePattern) !== false){
				return $secretNamePattern;
			}
		}
		return null;
	}
	public static function containsSecretValue(string $str): ?string{
		$secrets = self::getSecretValues();
		foreach($secrets as $secret){
			if(strpos($str, $secret) !== false){
				return $secret;
			}
		}
		return null;
	}
	/**
	 * @param string $str
	 * @param string|null $type
	 * @throws SecretException
	 */
	public static function exceptionIfContainsSecretValue(string $str, string $type){
		if($secret = self::containsSecretValue($str)){
			$truncatedSecret = QMStr::truncate($secret, 4);
			$obfuscatedStr = QMStr::truncate(self::obfuscateString($str), 140);
			throw new SecretException($secret, $str, $type,
				"$type should not contain $truncatedSecret but is $obfuscatedStr");
		}
	}
    public static function replaceSecretsInFiles(){
        $files = FileFinder::listFiles(abs_path('.'), true);
        $secrets = self::getSecretValues();
        foreach ($files as $file){
            $original = $contents = file_get_contents($file);
            foreach ($secrets as $secret){
                $contents = str_replace($secret, "[SECRET]", $contents);
            }
            if($original !== $contents){
                file_put_contents($file, $contents);
            }
        }
    }
	/**
	 * @param object $object
	 * @return object
	 */
	public static function obfuscateObject(object $object): object{
		foreach($object as $keyName => $value){
			if(is_object($value)){
				$object->$keyName = self::obfuscateObject($value);
			} elseif(is_array($value)){
				$object->$keyName = self::obfuscateArray($value);
			} elseif(is_string($value)){
				$object->$keyName = self::obfuscateString($value, $keyName);
			}
		}
		return $object;
	}
	/**
	 * @param mixed $str
	 * @param string|null $name
	 * @return mixed
	 */
	public static function obfuscateString(string $str, string $name = null): string{
		$ignore = [
			QMAccessToken::INVALID_TOKEN_EXCEPTION,
		];
		if(in_array($str, $ignore)){
			return $str;
		}
		// Uncomment for debugging logging issues QMLogger::cli()->info(__METHOD__." calling obfuscateSecretValues...");
		$str = self::obfuscateSecretValues($str);
		if(stripos($str, 'token=') !== false){
			return QMStr::before('token=', $str) . '_TRUNCATED_TOKEN_';
		}
		if($name){
			if($pattern = self::findSecretNamePattern($name)){
				return "_OBFUSCATED_BECAUSE_IT_THE_VALUE_NAME_{$name}_CONTAINED_{$pattern}_";
			}
		}
		if($secretWord = self::findSecretNamePattern($str)){
			$short = substr($secretWord, 0, 5) . '...';
			$truncated = substr($str, 0, 5);
			return $truncated . "_OBFUSCATED_BECAUSE_IT_CONTAINED_{$short}_";
		}
		return $str;
	}
	/**
	 * @param $str
	 * @return string
	 */
	private static function obfuscateSecretValues($str): string{
		if(!is_string($str)){
			return $str;
		}
		// Uncomment for debugging logging issues QMLogger::cli()->info(__METHOD__." calling truncate...");
		$truncated = QMStr::truncate($str, 4);
		// Uncomment for debugging logging issues QMLogger::cli()->info(__METHOD__." calling getSecretValues...");
		foreach(self::getSecretValues() as $secretValue){
			$str = str_replace($secretValue, $truncated . "_TRUNCATED_BEC", $str);
		}
		return $str;
	}
	/**
	 * @param object $object
	 * @return string
	 */
	public static function obfuscateAndJsonEncodeObject(object $object): string{
		$object = self::obfuscateObject($object);
		return QMStr::prettyJsonEncode($object);
	}
	/**
	 * @param array $array
	 * @return array
	 */
	public static function obfuscateArray(array $array): array{
		foreach($array as $key => $value){
			if(!$value){continue;}
			if(is_array($value)){
				$array[$key] = self::obfuscateArray($value);
			} elseif(is_object($value)){
				$array[$key] = self::obfuscateObject($value);
			} else{
				$array[$key] = self::obfuscateString($value, $key);
			}
		}
		return $array;
	}
	/**
	 * @param string $str
	 * @param string $type
	 * @throws SecretException
	 */
	public static function assertDoesNotContainSecrets(string $str, string $type){
		self::exceptionIfContainsSecretValue($str, $type);
	}
	public static function replaceHardCodedEnvs(){
		$files = EnvFile::allQMEnvs();
		foreach($files as $file){
			$values = $file->getEnvs();
			foreach($values as $name => $value){
				if(self::isSecretyName($name)){
					FileHelper::replaceInProjectFiles('"' . $value . '"', "\App\Utils\Env::get('$name')", '.php');
					FileHelper::replaceInProjectFiles("'$value'", "\App\Utils\Env::get('$name')", '.php');
				}
			}
		}
	}
	/**
	 * @param array $secretValues
	 */
	private static function validateSecretValues(array $secretValues): void{
		$github = GithubConnector::getClientSecret();
		if(!in_array($github, $secretValues)){
			if(EnvOverride::isLocal()){
				QMLog::print(QMConnector::getClientSecrets(), "client secrets");
				QMLog::print($secretValues, "secret values");
				QMLog::print($_ENV, "_ENV");
			}
			ConsoleLog::warning(__FUNCTION__ . " is not working! ");
		}
	}
	/**
	 * @param string $s3Path
	 * @return string|null
	 */
	public static function containsSecretFilePattern(string $s3Path): ?string{
		$secretFilePatterns = self::SECRET_FILE_PATTERNS;
		foreach($secretFilePatterns as $filePattern){
			if(stripos($s3Path, $filePattern) !== false){
				return $filePattern;
			}
		}
		return null;
	}
}
