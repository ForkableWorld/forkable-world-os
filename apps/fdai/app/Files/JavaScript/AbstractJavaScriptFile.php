<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\JavaScript;
use App\Files\TypedProjectFile;
abstract class AbstractJavaScriptFile extends TypedProjectFile {
	public const SWEETALERT2 = 'https://cdn.jsdelivr.net/npm/sweetalert2@9';
	public static function getFolderPaths(): array{
		return ['public/js'];
	}
	public static function getDefaultExtension(): string{
		return "js";
	}
}
