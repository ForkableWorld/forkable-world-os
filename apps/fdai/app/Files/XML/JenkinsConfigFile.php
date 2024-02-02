<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\XML;
use App\DevOps\Jenkins\Jenkins;
use App\Files\UntypedFile;
use App\Files\XmlFile;
use App\Repos\JenkinsBackupRepo;
use App\Types\QMStr;
class JenkinsConfigFile extends XmlFile {
	public function getDefinedOwner(): string{ return Jenkins::JENKINS; }
	public function getDefinedGroup(): string{ return Jenkins::JENKINS; }
	public function getDefinedPermissions(): string{ return UntypedFile::$permissions['file']['public']; }
	public static function getDiskName(): string{
		return Jenkins::JENKINS;
	}
	/**
	 * @param string $contents
	 */
	public function writeContents(string $contents): string {
		$this->ownFile();
		parent::writeContents($contents);
	}
	public function getRepoPath(): string{
		$path = QMStr::removeIfFirstCharacter("/", $this->getRealPath());
		return JenkinsBackupRepo::getAbsolutePath($path);
	}
	public function getRelativePath(): string{
		$prefix = $this->getPathPrefix();
		$abs = $this->getRealPath();
		$rel = str_replace($prefix, "", $abs);
		return $rel;
	}
}
