<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\FileTraits;
use App\UI\HtmlHelper;
use Illuminate\Support\Facades\View;
trait HasFileTemplate {
	abstract public function __toString();
	/**
	 * @return mixed|string
	 */
	private function render(){
		$str = HtmlHelper::renderBlade($this->getView());
		return $str;
	}
	public function renderAndSave(): void{
		$str = $this->render();
		//$str = str_replace("# YOUR_CODE_GOES_HERE", $main, $str);
		$this->saveNewContents($str);
	}
	abstract protected function getView(): View;
}
