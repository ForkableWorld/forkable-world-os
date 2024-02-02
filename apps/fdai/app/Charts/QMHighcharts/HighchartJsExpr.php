<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */
namespace App\Charts\QMHighcharts;
class HighchartJsExpr {
	/**
	 * The javascript expression
	 * @var string
	 */
	public $_expression;
	/**
	 * The HighchartJsExpr constructor
	 * @param string $expression The javascript expression
	 */
	public function __construct(string $expression){
		//if(stripos($expression){le("function") === false);}
		$this->_expression = iconv(mb_detect_encoding($expression), "UTF-8", $expression);
	}
	/**
	 * Returns the javascript expression
	 * @return string The javascript expression
	 */
	public function getExpression(){
		return $this->_expression;
	}
}
