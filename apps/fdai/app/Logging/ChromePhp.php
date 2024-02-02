<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use ReflectionClass;
use ReflectionProperty;
/** Copyright 2010-2013 Craig Campbell
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/** Server Side Chrome PHP debugger class
 * @package ChromePhp
 * @author Craig Campbell <iamcraigcampbell@gmail.com>
 */
class ChromePhp
{
    /**
     * @var string
     */
    const VERSION = '4.1.0';
    /**
     * @var string
     */
    const HEADER_NAME = 'X-ChromeLogger-Data';
    /**
     * @var string
     */
    const BACKTRACE_LEVEL = 'backtrace_level';
    /**
     * @var string
     */
    const LOG = 'log';
    /**
     * @var string
     */
    const WARN = 'warn';
    /**
     * @var string
     */
    const ERROR = 'error';
    /**
     * @var string
     */
    const GROUP = 'group';
    /**
     * @var string
     */
    const INFO = 'info';
    /**
     * @var string
     */
    const GROUP_END = 'groupEnd';
    /**
     * @var string
     */
    const GROUP_COLLAPSED = 'groupCollapsed';
    /**
     * @var string
     */
    const TABLE = 'table';
    /**
     * @var string
     */
    protected $_php_version;
    /**
     * @var int
     */
    protected $_timestamp;
    /**
     * @var array
     */
    protected $_json = [
        'version' => self::VERSION,
        'columns' => ['log', 'backtrace', 'type'],
        'rows' => []
    ];
    /**
     * @var array
     */
    protected $_backtraces = [];
    /**
     * @var bool
     */
    protected $_error_triggered = false;
    /**
     * @var array
     */
    protected $_settings = [
        self::BACKTRACE_LEVEL => 1
    ];
    /**
     * @var ChromePhp
     */
    protected static $_instance;
    /**
     * Prevent recursion when working with objects referring to each other
     *
     * @var array
     */
    protected $_processed = [];
    /**
     * constructor
     */
    private function __construct(){
        $this->_php_version = phpversion();
        $this->_timestamp = $this->_php_version >= 5.1 ? $_SERVER['REQUEST_TIME'] : time();
        $this->_json['request_uri'] = $_SERVER['REQUEST_URI'] ?? "No request uri for ".__METHOD__;
    }
    /**
     * gets instance of this class
     *
     * @return ChromePhp
     */
    public static function getInstance(): ChromePhp{
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     * logs a variable to the console
     * @return void
     */
    public static function log(){
        $args = func_get_args();
        self::_log('', $args);
    }
    /**
     * logs a warning to the console
     *
     * @param mixed $data,... unlimited OPTIONAL number of additional logs [...]
     * @return void
     */
    public static function warn(){
        $args = func_get_args();
        self::_log(self::WARN, $args);
    }
    /**
     * logs an error to the console
     *
     * @param mixed $data,... unlimited OPTIONAL number of additional logs [...]
     * @return void
     */
    public static function error(){
        $args = func_get_args();
        self::_log(self::ERROR, $args);
    }
    /**
     * sends a group log
     *
     * @param string value
     */
    public static function group(){
        $args = func_get_args();
        self::_log(self::GROUP, $args);
    }
    /**
     * sends an info log
     *
     * @param mixed $data,... unlimited OPTIONAL number of additional logs [...]
     * @return void
     */
    public static function info(){
        $args = func_get_args();
        self::_log(self::INFO, $args);
    }
    /**
     * sends a collapsed group log
     *
     * @param string value
     */
    public static function groupCollapsed(){
        $args = func_get_args();
        self::_log(self::GROUP_COLLAPSED, $args);
    }
    /**
     * ends a group log
     *
     * @param string value
     */
    public static function groupEnd(){
        $args = func_get_args();
        self::_log(self::GROUP_END, $args);
    }
    /**
     * sends a table log
     *
     * @param string value
     */
    public static function table(){
        $args = func_get_args();
        self::_log(self::TABLE, $args);
    }
	/**
	 * internal logging call
	 * @param string $type
	 * @param array $args
	 * @return void
	 */
    public static function _log(string $type, array $args){
        // nothing passed in, don't do anything
        if (count($args) == 0 && $type != self::GROUP_END) {
            return;
        }
        $logger = self::getInstance();
        $logger->_processed = [];
        $logs = [];
        foreach ($args as $arg) {
            $logs[] = $logger->formatAndCompressLogMeta($arg);
        }
        $backtrace = debug_backtrace(false);
        $level = $logger->getSetting(self::BACKTRACE_LEVEL);
        $backtrace_message = 'unknown';
        if (isset($backtrace[$level]['file']) && isset($backtrace[$level]['line'])) {
            $backtrace_message = $backtrace[$level]['file'] . ' : ' . $backtrace[$level]['line'];
        }
        $logger->_addRow($logs, $backtrace_message, $type);
    }
	/**
	 * converts an object to a better format for logging
	 * @param Object
	 * @return array|string
	 */
	protected function formatAndCompressLogMeta($object){
        // if this isn't an object then just return it
        if (!is_object($object)) {
            return $object;
        }
        //Mark this object as processed so we don't convert it twice and it
        //Also avoid recursion when objects refer to each other
        $this->_processed[] = $object;
        $object_as_array = [];
        // first add the class name
        $object_as_array['___class_name'] = get_class($object);
        // loop through object vars
        $object_vars = get_object_vars($object);
        foreach ($object_vars as $key => $value) {
            // same instance as parent object
            if ($value === $object || in_array($value, $this->_processed, true)) {
                $value = 'recursion - parent object [' . get_class($value) . ']';
            }
            $object_as_array[$key] = $this->formatAndCompressLogMeta($value);
        }
        $reflection = new ReflectionClass($object);
        // loop through the properties and add those
        foreach ($reflection->getProperties() as $property) {
            // if one of these properties was already added above then ignore it
            if (array_key_exists($property->getName(), $object_vars)) {
                continue;
            }
            $type = $this->_getPropertyKey($property);
            if ($this->_php_version >= 5.3) {
                $property->setAccessible(true);
            }
	        $value = $property->getValue($object);
	        // same instance as parent object
            if ($value === $object || in_array($value, $this->_processed, true)) {
                $value = 'recursion - parent object [' . get_class($value) . ']';
            }
            $object_as_array[$type] = $this->formatAndCompressLogMeta($value);
        }
        return $object_as_array;
    }
    /**
     * takes a reflection property and returns a nicely formatted key of the property name
     * @param ReflectionProperty $property
     * @return string
     */
    protected function _getPropertyKey(ReflectionProperty $property): string{
        $static = $property->isStatic() ? ' static' : '';
        if ($property->isPublic()) {
            return 'public' . $static . ' ' . $property->getName();
        }
        if ($property->isProtected()) {
            return 'protected' . $static . ' ' . $property->getName();
        }
        if ($property->isPrivate()) {
            return 'private' . $static . ' ' . $property->getName();
        }
        throw new \LogicException("NO _getPropertyKey");
    }
    /**
     * adds a value to the data array
     * @param array $logs
     * @param $backtrace
     * @param $type
     * @return void
     */
    protected function _addRow(array $logs, $backtrace, $type){
        // if this is logged on the same line for example in a loop, set it to null to save space
        if (in_array($backtrace, $this->_backtraces)) {
            $backtrace = null;
        }
        // for group, groupEnd, and groupCollapsed
        // take out the backtrace since it is not useful
        if ($type == self::GROUP || $type == self::GROUP_END || $type == self::GROUP_COLLAPSED) {
            $backtrace = null;
        }
        if ($backtrace !== null) {
            $this->_backtraces[] = $backtrace;
        }
        $row = [$logs, $backtrace, $type];
        if(AppMode::isApiRequest() && count($this->_json['rows']) > 10){
            return; // Too many duplicate logs cause header too big error
        }
        $this->_json['rows'][] = $row;
        try {
            $this->_writeHeader($this->_json);
        } catch (\Throwable $e){
            ConsoleLog::debug(__METHOD__.": ".$e->getMessage());
        }
    }
	/**
	 * @param $data
	 */
	protected function _writeHeader($data){
        header(self::HEADER_NAME . ': ' . $this->_encode($data));
    }
    /**
     * encodes the data to be sent along with the request
     * @param array $data
     * @return string
     */
    protected function _encode(array $data): string{
        return base64_encode(utf8_encode(json_encode($data)));
    }
	/**
	 * @param string $name
	 * @param string $messageLevel
	 * @param $meta
	 */
	public static function logIfLocalApiRequest(string $name, string $messageLevel, $meta){
        if(AppMode::isApiRequest() && EnvOverride::isLocal()){
            if(QMLog::getLogCount() < 10){ // Avoid too big header
            	$meta = $meta ?? [];
            	if(!is_array($meta)){
            		$meta = QMArr::toArray($meta);
	            }
                ChromePhp::_log($messageLevel,[
                    QMStr::truncate($name, 30),
                    QMArr::scalarOnly($meta),
                ]);
            }
        }
    }
    /**
     * adds a setting
     *
     * @param string key
     * @param mixed value
     * @return void
     */
    public function addSetting($key, $value){
        $this->_settings[$key] = $value;
    }
    /**
     * add ability to set multiple settings in one call
     *
     * @param array $settings
     * @return void
     */
    public function addSettings(array $settings){
        foreach ($settings as $key => $value) {
            $this->addSetting($key, $value);
        }
    }
    /**
     * gets a setting
     *
     * @param string key
     * @return mixed
     */
    public function getSetting($key){
        if (!isset($this->_settings[$key])) {
            return null;
        }
        return $this->_settings[$key];
    }
}
