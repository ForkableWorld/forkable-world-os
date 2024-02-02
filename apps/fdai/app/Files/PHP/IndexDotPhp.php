<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\PHP;
use App\Folders\PublicFolder;
class IndexDotPhp extends AbstractPhpFile {
	private static $instance;
	public function __construct(){
		parent::__construct(static::getDefaultFolderRelative()."/index.php");
	}
	public static function getDefaultFolderRelative(): string{
		return PublicFolder::FOLDER_PUBLIC;
	}
	public static function instance(): IndexDotPhp{
        return self::$instance ?? self::$instance = new IndexDotPhp();
    }
}
