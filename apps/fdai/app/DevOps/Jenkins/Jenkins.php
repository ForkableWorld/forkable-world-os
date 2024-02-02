<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection SpellCheckingInspection */
/** @noinspection HttpUrlsUsage */
namespace App\DevOps\Jenkins;
use App\Computers\JenkinsSlave;
use App\Computers\LightsailInstanceResponse;
use App\Computers\ThisComputer;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Properties\Base\BaseNameProperty;
use App\Repos\JenkinsBackupRepo;
use App\ShellCommands\CommandFailureException;
use App\ShellCommands\JenkinsCommands\JenkinsReload;
use App\ShellCommands\OfflineException;
use App\UI\ImageUrls;
use Illuminate\Support\Collection;
class Jenkins {
	public const API_BASE_URL = 'http://' . Jenkins::USER . ':' . Jenkins::API_TOKEN . '@' . Jenkins::HOST;
	public const API_TOKEN = "1192fd8b2fadd0d3d74cbb1df94c48753a";
	const EXCLUDE = [
		"changelog",
		"/builds/",
		"build.xml",
		"Result.xml",
		"_gsdata_",
	];
	const HOST = 'quantimodo2.asuscomm.com:8082';
	const IMAGE = ImageUrls::JENKINS;
	const JENKINS = "jenkins";
	const JENKINS_HOME_FOLDER = "/mnt/c/Jenkins/.jenkins";
	//const JENKINS_HOME_FOLDER = "/var/lib/jenkins";
	const JENKINS_NODES_FOLDER = self::JENKINS_HOME_FOLDER . "/nodes";
	const JENKINS_PATH = self::JENKINS_HOME_FOLDER;
	const JENKINS_SLAVE_WORKSPACE = "/var/jenkins/workspace";
	const JENKINS_URL = "http://" . self::HOST;
	public const USER = "quantimodo2";
	/**
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public static function fixPermissions(){
		$backupFolder = "~/jenkins-loose-permissions";
		(new ThisComputer)->deleteJenkinsNode($backupFolder);
		ThisComputer::copyFolder(self::JENKINS_HOME_FOLDER, $backupFolder);
		ThisComputer::setFolderOwnerAndPermissions(self::JENKINS_HOME_FOLDER, self::JENKINS, self::JENKINS, "644",
			"755");
	}
	/**
	 * @return string
	 */
	protected static function getJenkinsFolder(): string{
		return JenkinsBackupRepo::getSubfolder();
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	protected static function deleteBuildFolders(): void{
		FileHelper::deleteFoldersLike(self::JENKINS_HOME_FOLDER . "/jobs", "builds");
	}
	public static function getWorkspacePath(): string{ return self::JENKINS_SLAVE_WORKSPACE; }
	/**
	 * @throws QMFileNotFoundException
	 */
	public static function outputFailedCypressLogs(){
		$folder = FileFinder::getMostRecentlyModifiedFolder(self::JENKINS_HOME_FOLDER."/jobs/cypress-tests/builds");
		$files = FileFinder::getFilesInFolder($folder);
		foreach($files as $file){
			QMLog::info($file);
			ConsoleLog::info(file_get_contents($file));
		}
	}
	public static function createJenkinsNode(string $name, string $host, string $labels, string $userName): void{
		$labels = trim($labels);
		$nodesFolder = self::JENKINS_NODES_FOLDER;
		$path = "$nodesFolder/$name/config.xml";
		FileHelper::write($path, "<?xml version='1.1' encoding='UTF-8'?>
	<slave>
	<name>$name</name>
	<description>http://$host:8888</description>
	<remoteFS>/home/ubuntu</remoteFS>
	<numExecutors>1</numExecutors>
	<mode>NORMAL</mode>
	<retentionStrategy class=\"hudson.slaves.RetentionStrategy\$Always\"/>
	<launcher class=\"hudson.plugins.sshslaves.SSHLauncher\" plugin=\"ssh-slaves@1.31.4\">
	<host>$host</host>
	<port>22</port>
	<credentialsId>$userName-aws-pem</credentialsId>
	<javaPath>/usr/bin/java</javaPath>
	<prefixStartSlaveCmd>sudo apt-get -y install openjdk-8-jre || true</prefixStartSlaveCmd>
	<launchTimeoutSeconds>60</launchTimeoutSeconds>
	<maxNumRetries>10</maxNumRetries>
	<retryWaitTime>15</retryWaitTime>
	<sshHostKeyVerificationStrategy class=\"hudson.plugins.sshslaves.verifiers.NonVerifyingKeyVerificationStrategy\"/>
	<tcpNoDelay>true</tcpNoDelay>
	</launcher>
	<label>$labels</label>
	<nodeProperties/>
	</slave>");
//		$master = new MasterComputer();
//		if()
//		$u = $master->sshOffline();
//		$master->upload($path, $path);
//		$master->chown($path, self::JENKINS);
	}
	public static function setNodesFolderPermissions(): void{
		ThisComputer::setFolderOwnerAndPermissions(self::JENKINS_NODES_FOLDER, self::JENKINS, self::JENKINS);
		(new JenkinsReload)->execute();
	}
	public static function restart(){
		ThisComputer::exec("sudo service jenkins restart");
	}
	/**
	 * @param string|null $prefix
	 * @return void
	 */
	public static function createJenkinsNodes(string $prefix = null): void{
		$instances = LightsailInstanceResponse::allFromMemoryOrApi();
		if($prefix){
			$instances = BaseNameProperty::filterWhereStartsWith($prefix, $instances);
		}
		foreach($instances as $instance){
			$instance->createJenkinsNode(false);
		}
		//JenkinsBackupRepo::cloneIfNecessary();
		(new JenkinsReload)->execute();
	}
	public static function notifyToRestart(string $body){
		QMLog::notifyLink("Restart Jenkins", self::getRestartUrl(), $body, self::IMAGE);
	}
	public static function getRestartUrl(): string{
		return self::getJenkinsUrl() . "/restart";
	}
	public static function getJenkinsUrl(): string{
		return self::JENKINS_URL;
	}
	/**
	 * @return Collection|JenkinsSlave[]
	 */
	public static function getComputers(): Collection{
		return JenkinsSlave::getJenkinsSlaves();
	}
	public static function getJenkinsPath(): string{
		return self::JENKINS_PATH;
	}
	public static function currentJobNameContains(string $string): bool{
		$name = self::getCurrentJobName();
		if(!$name){
			return false;
		}
		return stripos($name, $string) !== false;
	}
	public static function getCurrentJobName(): string{
		return JenkinsJob::getCurrentJobName();
	}
	/**
	 * @param string $url
	 * @return false|resource|mixed
	 * @noinspection PhpReturnDocTypeMismatchInspection
	 */
	public static function post(string $url){
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, 1);
		$headers = [];
		if(JenkinsAPI::areCrumbsEnabled()){
			$headers[] = JenkinsAPI::getCrumbHeader();
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);
		return $curl;
	}
}
