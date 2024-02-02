<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\FileTraits;
use App\Files\UntypedFile;
use App\Folders\DynamicFolder;
use Illuminate\Support\Collection;
trait IsBackedUpFolder {
	public function backup(){
		$inDestination = $this->getFilesInDestination();
		foreach($inDestination as $dstFile){
			$sourceFile = new UntypedFile($this->getPath() . "/" . $dstFile->getName());
			$sourceFile->copy($this->getDestinationFolderPath(), $this->getDestinationOwner(),
				$this->getDestinationOwner());
		}
	}
	public function getDestinationPermissions(): string{ return UntypedFile::$permissions['dir']['private']; }
	abstract public function getDestinationOwner(): string;
	/**
	 * @return Collection
	 */
	private function getFilesInDestination(): Collection{
		return $this->getDestinationFolder()->getFiles();
	}
	public function getDestinationFolder(): DynamicFolder{
		return new DynamicFolder($this->getDestinationFolderPath());
	}
	abstract public function getDestinationFolderPath(): string;
}
