<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\PHP;
class TraitFile extends PhpClassFile {
	/**
	 * @return int
	 */
	protected function getBodyStartingLine(): int{
		$bodyStartsAt = $this->getFirstLineNumberContaining("trait ") + 1;
		if($this->lineContains($bodyStartsAt, "{")){
			$bodyStartsAt++;
		}
		return $bodyStartsAt;
	}
}
