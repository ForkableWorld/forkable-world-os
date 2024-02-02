<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request\Unit;
use App\Slim\QMSlim;
use App\Slim\View\Request\Request;
/** Class ListUnitRequest
 * @package App\Slim\View\Request\Unit
 */
class ListUnitRequest extends Request {
	/**
	 * @var string The name of the unit to get.
	 */
	private $unitName;
	/**
	 * @var string The abbreviated name of the unit to get.
	 */
	private $unitAbbreviatedName;
	/**
	 * @var string The unit category name the unit must be in.
	 */
	private $categoryName;
	/**
	 * Populate this request's properties from an Application instance.
	 * @param QMSlim $app
	 */
	public function populate(QMSlim $app){
		$this->setApplication($app);
		$this->setUnitName($this->getParam('unitName', null));
		$this->setUnitAbbreviatedName($this->getParam('unitAbbreviatedName', null));
		$this->setCategoryName($this->getParam('categoryName', null));
	}
	/**
	 * @return string
	 */
	public function getUnitName(){
		return $this->unitName;
	}
	/**
	 * @return string
	 */
	public function getUnitAbbreviatedName(){
		return $this->unitAbbreviatedName;
	}
	/**
	 * @return string
	 */
	public function getCategoryName(){
		return $this->categoryName;
	}
	/**
	 * @param string $unitName
	 */
	private function setUnitName($unitName){
		$this->unitName = $unitName;
	}
	/**
	 * @param string $unitAbbreviatedName
	 */
	private function setUnitAbbreviatedName($unitAbbreviatedName){
		$this->unitAbbreviatedName = $unitAbbreviatedName;
	}
	/**
	 * @param string $categoryName
	 */
	private function setCategoryName($categoryName){
		$this->categoryName = $categoryName;
	}
}
