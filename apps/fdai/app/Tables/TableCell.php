<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\UI\HtmlHelper;
use App\UI\QMColor;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Color;
class TableCell {
	public $value;
	public $backgroundColor;
	public $url;
	private $imagePath;
	private $tooltip;
	/**
	 * TableCell constructor.
	 * @param $value
	 * @param string|null $color
	 * @param string|null $url
	 * @param string $imagePath
	 */
	public function __construct($value, string $color = null, string $url = null, string $imagePath = null){
		$this->value = $value;
		if($color){
			$this->backgroundColor = $color;
		}
		if($url){
			$this->url = $url;
		}
		if($imagePath){
			$this->imagePath = $imagePath;
		}
	}
	/**
	 * @return string
	 */
	public function getStyle(): string{
		$style = "padding: 5px;";
		if($this->backgroundColor){
			$style .= " background-color: $this->backgroundColor;";
		}
		return $style;
	}
	/**
	 * @return string
	 */
	public function toHtml(): string{
		$value = $this->getValue();
		$tooltip = '';
		// PDF tooltip looks crappy
		//if($this->tooltip){$tooltip = ' <annotation content="'.$this->tooltip.'" icon="Help" /> ';}
		if($this->url){
			$value = HtmlHelper::getLinkAnchorHtml($value, $this->getUrl());
		}
		return '
            <td style="' . $this->getStyle() . '">
                ' . $value . $tooltip . '
            </td>';
	}
	/**
	 * @return string
	 */
	public function getValue(): string{
		return $this->value;
	}
	/**
	 * @return string|null
	 */
	public function getBackgroundHex(): ?string{
		return $this->backgroundColor;
	}
	/**
	 * @return string|null
	 */
	public function getBackgroundColorRGB(): ?string{
		return self::toRGB($this->getBackgroundHex());
	}
	/**
	 * @param string $hex
	 * @return string
	 */
	private static function toRGB(string $hex): string{
		[
			$r,
			$g,
			$b,
		] = sscanf($hex, "#%02x%02x%02x");
		return "$r$g$b";
	}
	/**
	 * @param Cell $cell
	 * @throws Exception
	 */
	public function toSpreadsheetCell(Cell $cell){
		$cell->setValue($this->value);
		if($this->backgroundColor){
			$cell->getStyle()->getFill()->getStartColor()->setRGB($this->getBackgroundColorRGB());
		}
		if($this->url){
			$link = new Hyperlink($this->url, $this->tooltip);
			$cell->setHyperlink($link);
			$color = new Color(self::toRGB(QMColor::HEX_GOOGLE_BLUE));
			$cell->getStyle()->getFont()->setColor($color);
		}
	}
	/**
	 * @return string
	 */
	public function getUrl(): ?string{
		return $this->url;
	}
	/**
	 * @param string $url
	 */
	public function setUrl(string $url): void{
		$this->url = $url;
	}
	/**
	 * @return string|null
	 */
	public function getBackgroundColor(): ?string{
		return $this->backgroundColor;
	}
	/**
	 * @param string|null $backgroundColor
	 */
	public function setBackgroundColor(?string $backgroundColor): void{
		$this->backgroundColor = $backgroundColor;
	}
	/**
	 * @return string
	 */
	public function getImagePath(): string{
		return $this->imagePath;
	}
	/**
	 * @param string $imagePath
	 */
	public function setImagePath(string $imagePath): void{
		$this->imagePath = $imagePath;
	}
	/**
	 * @return string
	 */
	public function getTooltip(): string{
		return $this->tooltip;
	}
	/**
	 * @param string $tooltip
	 */
	public function setTooltip(string $tooltip): void{
		$this->tooltip = $tooltip;
	}
}
