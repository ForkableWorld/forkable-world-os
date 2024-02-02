<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\Firebase;
use Exception;
use Firebase\FirebaseLib;
use App\Storage\Memory;
use App\Storage\MemoryOrRedisCache;
use App\Logging\QMLog;
use App\Types\QMStr;
class FirebaseGlobalPermanent {
    public static $DEFAULT_URL = 'https://quantimo-do.firebaseio.com/';
    /**
     * @return FirebaseLib
     */
    public static function fb(): FirebaseLib {
        $url = \App\Utils\Env::get('FIREBASE_URL') ?? static::$DEFAULT_URL;
        if(!$url) {
            le("No FIREBASE_URL found");
        }
        $t = \App\Utils\Env::get('FIREBASE_TOKEN');
        if(!$t) {
            le("No FIREBASE_TOKEN found");
        }
        return new FirebaseLib($url, $t);
    }
    public static function url(string $key): string {
        return "https://console.firebase.google.com/project/quantimo-do/database/quantimo-do/data/$key";
    }
    /**
     * Pushing data into Firebase with a POST request
     * HTTP 200: Ok
     * @param string $path Path
     * @param mixed $data Data
     * @param array $options Options
     * @return array|null
     */
    public function push(string $path, $data, array $options = []): ?array{
        Memory::set($path, $data);
        return static::fb()->push($path, $data, $options);
    }
    /**
     * Updating data into Firebase with a PATH request
     * HTTP 200: Ok
     * @param string $path Path
     * @param mixed $data Data
     * @param array $options Options
     * @return array
     */
    public function update(string $path, $data, array $options = []): array{
        Memory::set($path, $data);
        return static::fb()->update($path, $data, $options);
    }
    /**
     * @param string $key
     * @param $value
     * @return array|bool
     * @throws \App\Exceptions\TooBigForCacheException
     */
    public static function set(string $key, $value){
        $key = static::formatKey($key, false);
        Memory::set($key, $value);
        $value = MemoryOrRedisCache::shrinkAndValidateSize($key, $value);
        if(!$value){
            return false;
        }
        try {
            $url = self::url($key);
            QMLog::debug("Setting $url on FireBase...");
            if(is_object($value)){
                $jsonData = json_encode($value);
                if($jsonData === "{}"){
                    throw new \LogicException("$key data will be empty after firebase does json_encode! ".
                        "Convert the object to an array or something before storing");
                }
            }
            $response = static::fb()->set($key, $value);
            return self::checkResponseForErrors($response, $key, $value);
        } catch (Exception $e) {
            QMLog::error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
    /**
     * @param string $key
     * @param bool $getAsArray
     * @return array|bool
     */
    public static function get(string $key, bool $getAsArray = false){
        $key = static::formatKey($key);
        try {
            $mem = Memory::get($key,Memory::MISCELLANEOUS);
            if($mem !== null){return $mem;}
            $url = static::url($key);
            QMLog::infoWithoutObfuscation("Getting from $url...");
            $firebaseLib = static::fb();
            $response = json_decode($firebaseLib->get($key));
            $data = self::checkResponseForErrors($response, $key);
            if($getAsArray){$data = json_decode(json_encode($data), true);}
            return $data;
        } catch (Exception $e) {
            QMLog::error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
    /**
     * @param string $key
     * @param bool $temporary
     * @return array|bool
     */
    public static function delete(string $key){
        $key = static::formatKey($key, false);
        try {
            Memory::set($key, null);
            $response = static::fb()->delete($key, []);
            return self::checkResponseForErrors($response, $key);
        } catch (Exception $e) {
            QMLog::error($e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
    /**
     * @param $response
     * @param string $key
     * @param null $value
     * @return mixed
     */
    private static function checkResponseForErrors($response, string $key, $value = null){
        if($response === false){QMLog::error("Firebase response for $key is false!");}
        $response = QMStr::jsonDecodeIfNecessary($response);
        if(isset($response->error)){
            QMLog::error("Firebase error for $key: " . $response->error);
            if($value){QMLog::error(QMLog::var_export($value, true));}
        }
        return $response;
    }
    /**
     * @param string $path
     * @param bool $temporary
     * @return string
     */
    public static function formatKey(string $path, bool $temporary = false): string{
        $path = str_replace('";s:', '', $path);
        $path = str_replace('"', '', $path);
        $path = str_replace(':', '', $path);
        $path = str_replace(';', '', $path);
        $path = str_replace(' ', '', $path);
        $path = str_replace('{', '', $path);
        $path = str_replace('}', '', $path);
        $path = str_replace('@', '', $path);
        $path = str_replace('.', '', $path);
        if($temporary){$path = 'temp/'.$path;}
        return $path;
    }
}
