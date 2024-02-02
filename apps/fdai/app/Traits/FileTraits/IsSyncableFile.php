<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\FileTraits;
use App\Files\FileHelper;
use App\Folders\DynamicFolder;
trait IsSyncableFile {
	protected $perms;
	public function sync(){
		$dest = $this->getDestinationPath();
		$this->copy($dest, $this->getDestinationPerms(), $this->getDestinationOwner());
	}
	/**
	 * @return string
	 */
	public function getDestinationPath(): string{
		$dest = $this->getDestinationFolderPath() . "/" . $this->getFileName();
		return $dest;
	}
	abstract public function getDestinationFolderPath(): string;
	abstract public function getDestinationPerms(): string;
	abstract public function getDestinationOwner(): string;
	public function getDestinationFolder(): DynamicFolder{
		return DynamicFolder::findOrNew($this->getDestinationFolderPath());
	}
	public function getPath(): string{
        $path = FileHelper::absPath($this->getSourcePath());
        $this->setPath($path);
        return $path;
	}
	abstract public function getSourcePath(): string;
}
