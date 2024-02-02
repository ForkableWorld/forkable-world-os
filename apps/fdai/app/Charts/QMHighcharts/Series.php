<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseMarker;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Types\QMStr;
class Series extends BaseSeries {
	/**
	 * @var float
	 */
	public $linkOpacity;
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(string $unitName = null){
		if($unitName){
			$this->setUnitName($unitName);
		}
		$this->marker = new BaseMarker(); // marker needed so we can see anything on single point highstock charts
	}
	/**
	 * @return string
	 */
	public function getId(): string{
		if(!$this->id){
			$this->id = QMStr::slugify($this->name);
		}
		return $this->id;
	}
	public function getTitleAttribute(): string{
		return $this->name;
	}
	/**
	 * @return string
	 */
	public function getColor(): string{
		if(!$this->color){
			le("set color");
		}
		return $this->color;
	}
	public function setNameAndId(string $name){
		$this->id = QMStr::slugify($name);
		$this->name = $name;
	}
	/**
	 * @param string $unitName
	 */
	public function setUnitName(string $unitName): void{
		//if($this->name && stripos($this->name, $unitName) === false){$this->name .= " ($unitName)";}
		$this->setTooltipFormatter($unitName);
	}
	public function setColor(string $color){
		$this->color = $color;
	}
	public function validate(): void {
		lei(is_object($this->yAxis), "yAxis should be int", $this->yAxis);
	}
	/**
	 * @param int|null $index
	 */
	public function setYAxisIndex(?int $index): void{
		$this->yAxis = $index;
	}
	/**
	 * @return int
	 */
	public function getYAxisIndex(): ?int{
		return $this->yAxis;
	}
	/**
	 * @param string $unitName
	 */
	public function setTooltipFormatter(string $unitName): void{
		$this->tooltip = new Tooltip($unitName);
		$this->tooltip->setFormatter("
            return this.y +' $unitName $this->name';
");
	}
}
