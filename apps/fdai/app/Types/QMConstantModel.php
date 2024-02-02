<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Types;
use App\Logging\QMLog;
use Krlove\CodeGenerator\Model\ConstantModel;
class QMConstantModel extends ConstantModel {
	public function __construct(string $name, $value = null){
		if(!$value){
			$value = $name;
		}
		$name = self::toConstantName($name);
		parent::__construct($name, $value);
	}
	public static function toConstantName(string $string): string{
		return QMStr::toConstantName($string);
	}
	public static function log(string $name, $value = null){
		$me = new static($name, $value);
		$lines = $me->toLines();
		QMLog::info($lines);
	}

    /** @var bool */
    private $final = false;


    /** @return static */
    public function setValue($val): self
    {
        $this->value = $val;
        return $this;
    }


    public function getValue()
    {
        return $this->value;
    }


    /** @return static */
    public function setFinal(bool $state = true): self
    {
        $this->final = $state;
        return $this;
    }


    public function isFinal(): bool
    {
        return $this->final;
    }
}
