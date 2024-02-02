<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Spreadsheet;
use App\Files\TypedProjectFile;
use App\Repos\ReferenceDataRepo;
use App\Types\QMArr;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
abstract class AbstractSpreadsheetFile extends TypedProjectFile {
	protected Spreadsheet $spreadsheet;
	public static function getFolderPaths(): array{
		return [
			ReferenceDataRepo::getAbsolutePath(),
		];
	}
	public function getSpreadsheet(): Spreadsheet{
		return $this->spreadsheet ?? $this->spreadsheet = IOFactory::load($this->getRealPath
			());
	}
	/**
	 * @return Collection  Associative array from header values
	 */
	public function getData(): Collection{
		return collect(QMArr::headerToAssociativeArray($this->getSpreadsheet()->getActiveSheet()->toArray()));
	}
}
