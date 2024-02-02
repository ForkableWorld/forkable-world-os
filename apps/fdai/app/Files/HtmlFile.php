<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */
namespace App\Files;
use App\Exceptions\DiffException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\TestArtifacts\IsTestArtifactFile;
class HtmlFile extends UntypedFile
{
	use IsTestArtifactFile;
	public static function getData(){
		// TODO: Implement getData() method.
	}
	/**
	 * @param string $key
	 * @param string $new
	 * @param string $message
	 * @param bool $ignoreNumbers
	 * @throws DiffException
	 * @throws QMFileNotFoundException
	 */
	public static function assertSameHtml(string $key, string $new, string $message = '',
	                                      bool $ignoreNumbers = false): void{
		static::compareFile("$key.html", $new, $message, $ignoreNumbers);
	}
}
