<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\DevOps\Jenkins\Jenkins;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Files\QMSync;
use App\Files\XML\JenkinsConfigFile;
use App\ShellCommands\JenkinsCommands\JenkinsReload;
class JenkinsBackupRepo extends GitRepo {
	public const PUBLIC = true;
	public static $REPO_NAME = 'jenkins-backup';
	public const USERNAME = 'mikepsinn';
	public const DEFAULT_BRANCH = self::BRANCH_MASTER;
	private const SUBFOLDER = "var/lib/jenkins";
	public static function backupJenkins(){
		JenkinsBackupRepo::backupXmlFiles();
		foreach([
			'jobs',
			'nodes',
			'secrets',
			'Github',
			//'users' Permissions problems
		] as $folder){
			self::backupJenkinsFolder($folder);
		}
	}
	/**
	 * @param string $folder
	 * @deprecated I now symlink jenkins to a repo at "$qmApi\repos\mikepsinn\jenkins-backup\var\lib\jenkins"
	 */
	public static function backupJenkinsFolder(string $folder): void{
		$backupFolder = self::getSubfolder($folder);
		FileHelper::deleteFilesInFolder($backupFolder);
		QMSync::copyFilesRecursively(Jenkins::JENKINS_HOME_FOLDER . "/$folder", $backupFolder . "/$folder", ".xml",
			Jenkins::EXCLUDE);
	}
	public static function backupXmlFiles(): void{
		$backupFolder = self::getSubfolder();
		FileHelper::copyFiles(Jenkins::JENKINS_HOME_FOLDER, $backupFolder, ".xml", Jenkins::EXCLUDE);
	}
	public static function getSubfolder(string $folder = null): string{
		if(!$folder){
			return self::SUBFOLDER;
		}
		return self::SUBFOLDER . "/$folder";
	}
	private static function getRepoJobsFolder(): string{
		return self::getSubfolder("jobs");
	}
	public static function replaceLineInJobsConfigs(string $lineContains, string $replace){
		$configs = FileFinder::finder()->in(Jenkins::JENKINS_HOME_FOLDER . "/jobs")->notPath("/pullrequests/")
			->path("config.xml");
		$replaced = [];
		$all = $configs->getIterator();
		foreach($all as $config){
			$f = new JenkinsConfigFile($config->getRealPath());
			try {
				$f->replaceLineContaining($lineContains, $replace);
			} catch (QMFileNotFoundException $e) {
				$f->logInfo(__METHOD__.": ".$e->getMessage());
				continue;
			}
			$f->copy($f->getRepoPath());
			$replaced[$f->getPath()] = $f;
		}
		if($replaced){
			JenkinsReload::execute();
		}
	}
	public static function restore(){
		static::clonePullAndOrUpdateRepo();
	}
}
