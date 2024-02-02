<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors\Responses;
use App\CodeGenerators\CodeGenerator;
use App\Traits\HasClassName;
use App\Types\QMStr;
class BaseResponseObject
{
    use HasClassName;
	/**
	 * @param null $data
	 */
	public function __construct($data = null){
        if(!$data){return;}
        foreach($data as $key => $value){
            if(is_object($value)){
                $class = $this->getNameSpace().'\\'.QMStr::toClassName($key);
                if(!class_exists($class)){
                    $this->json2Code($key, $value);
                    le("Rerun so $key class is auto-loaded");
                }
                $this->$key = new $class($value);
            } elseif(is_array($value)){
                $this->$key = $value;
            } else {
                $this->$key = $value;
            }
        }
    }
    /**
     * @param string $key
     * @param $value
     */
    public function json2Code(string $key, $value){
        $class = QMStr::toClassName($key);
        CodeGenerator::jsonToBaseModel($this->getNameSpace() . "\\" . $class, $value);
    }
    /**
     * @param array $array
     * @return static
     */
    public static function __set_state(array $array) : self{
        $object = new static;
        foreach ($array as $key => $value) {
            $object->{$key} = $value;
        }
        return $object;
    }
}
