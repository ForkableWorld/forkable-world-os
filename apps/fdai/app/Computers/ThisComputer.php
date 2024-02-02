<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Computers;
use App\DevOps\Jenkins\Jenkins;
use App\DevOps\Jenkins\JenkinsAPI;
use App\Exceptions\InsufficientMemoryException;
use App\Exceptions\NoInternetException;
use App\Exceptions\NotFoundException;
use App\Files\FileHelper;
use App\Files\FilePermissionsException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Logging\QMLogLevel;
use App\PhpUnitJobs\JobTestCase;
use App\QMTerminal;
use App\Repos\QMAPIRepo;
use App\ShellCommands\CommandFailureException;
use App\ShellCommands\DynamicCommand;
use App\ShellCommands\OfflineException;
use App\Storage\DB\Writable;
use App\Storage\LocalFileCache;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\EnvOverride;
use App\Utils\UrlHelper;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use TitasGailius\Terminal\Response;
/**
 * @mixin \TitasGailius\Terminal\Builder
 * @staticMixin Builder
 */
class ThisComputer extends AbstractApiComputer {
	public const MB = 1000000;
	const API_MEMORY_LIMIT_MB = 512;
	//const API_MEMORY_LIMIT_MB = 4000;
	const WORKER_MEMORY_LIMIT = '4G';
	public const PHP_VERSION = "7.4.29";
	//public const LOCAL_HOST_NAME = 'localhost';
	public const LOCAL_HOST_NAME = 'local.quantimo.do';
    public const LOCAL_ORIGIN = 'https://'.self::LOCAL_HOST_NAME;
	const PRECISION = "14";
	const SERIALIZE_PRECISION = "-1";
	/**
	 * @var
	 */
	public static $externalIp;
	/**
	 * @var ThisComputer
	 */
	private static ThisComputer $instance;
	/**
	 */
	public function __construct(){
		parent::__construct();
		//$this->getIP();
		$this->displayName = $this->host = gethostname();
		$this->absoluteRemotePath = abs_path();
		$this->user = static::user();
	}
	/**
	 * @return string  BROKEN: file_get_contents(http://checkip.dyndns.com/): failed to open stream: HTTP request
	 *     failed!
	 * @throws NoInternetException
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function getCurrentServerExternalIp(): string{
//		if($ip = static::getNodeLabel(LightsailInstance::FIELD_PUBLIC_IP_ADDRESS)){
//			return self::$externalIp = $ip;
//		}
		if($runner = JenkinsSlave::getRunnerIP()){
			return self::$externalIp = $runner;
		}
		if(self::$externalIp !== null){
			return self::$externalIp;
		}
		// This only gets the internal IP for slaves if(!empty(Env::get('SSH_CONNECTION'))){return self::$externalIp = explode(" ", Env::get('SSH_CONNECTION'))[2];}
		$key = 'external_ip';
		if(self::$externalIp = LocalFileCache::get($key)){
			return self::$externalIp;
		}
		try {
			self::$externalIp = file_get_contents('http://ipecho.net/plain');
		} /** @noinspection PhpRedundantCatchClauseInspection */ catch (\ErrorException $e){
		    if(str_contains($e->getMessage(), "No such host is known")){
				throw new NoInternetException($e);
		    } else {
				throw $e;
		    }
		}
		LocalFileCache::set($key, self::$externalIp);
		return self::$externalIp;
	}
	public static function user(): string{
		return $_SERVER['USER'] ?? $_SERVER["USERNAME"] ?? get_current_user() ?? get_client_ip();
	}
	public static function getPrivateKey(): string{
		return "/home/".self::user()."/.ssh/id_rsa";
		//		if(!FileHelper::fileExists(self::SSH_PRIVATE_KEY_HOME)){
		//			le("Please copy private key to".self::SSH_PRIVATE_KEY_HOME);
		//			//FileHelper::copy(self::REPO_SSH_PRIVATE_KEY, self::SSH_PRIVATE_KEY_HOME);
		//			//static::chmod(self::SSH_PRIVATE_KEY_HOME, '600');
		//		}
		//		return self::SSH_PRIVATE_KEY_HOME;
	}
	/**
	 * @param string $destination
	 * @param string $perms
	 * @return void
	 */
	public static function chmod(string $destination, string $perms): void{
		$destination = abs_path($destination);
		self::sudoStatic("chmod $perms $destination");
	}
	/**
	 * @param string $cmd
	 * @return void
	 */
	public static function sudoStatic(string $cmd): void{
        try {
            self::exec("sudo $cmd");
        } catch (\Throwable $e) {
            if(stripos($e->getMessage(), "not found") !== false){
                self::exec($cmd);
            }
        }
	}
	public static function terminalRun(string $cmd): Response{
        return QMTerminal::run($cmd);
    }
	/**
	 * @param string $cmd
	 * @param bool $log
	 * @param bool $obfuscate
	 * @return DynamicCommand
	 */
	public static function exec(string $cmd, bool $log = false, bool $obfuscate = true): string {
		if(Env::get('DB_PASSWORD')){
			$obfuscated = str_replace(Writable::getPassword(), "[PASSWORD_HIDDEN]", $cmd);
		} else{
			$obfuscated = $cmd;
		}
		if($log){QMLog::infoWithoutContext($obfuscated, $obfuscate);}
		$response = self::terminalRun($cmd)->throw();
		$out = $response->output();
		if($out && $log){ConsoleLog::info($out, []);}
		return trim($out);
	}
	public static function removeMemoryLimit(){
		self::setMemoryLimit("-1");
	}
	/**
	 * @param string|null $limit
	 */
	private static function setMemoryLimit(string $limit = null){
		if($limit){
			$limit = str_replace("MB", "M", $limit);
		}
		$limitBefore = self::getMemoryLimit();
		if($limitBefore !== $limit){
			ini_set('memory_limit', $limit);
			$limitAfter = self::getMemoryLimit();
			if(AppMode::isAnyKindOfUnitTest() || !AppMode::isApiRequest()){
				$mb = memory_get_usage() / self::MB;
				ConsoleLog::info("Changed MEMORY_LIMIT from $limitBefore to $limitAfter (Current Usage: $mb MB)");
			}
		}
	}
	/**
	 * @return string
	 */
	public static function getMemoryLimit(): string{
		return ini_get('memory_limit');
	}
	public static function setAPIMemoryLimit(){
		$mem = self::API_MEMORY_LIMIT_MB;
		if($t = \App\Utils\AppMode::getCurrentTest()){
			$mem = $t->getApiMemoryLimit();
		}
		self::setMemoryLimit($mem."M");
		ThisComputer::outputMemoryUsageIfEnabledOrDebug();
	}
	/**
	 * @param string|null $message
	 */
	public static function outputMemoryUsageIfEnabledOrDebug(string $message = null){
		if(Env::get('OUTPUT_MEMORY_USAGE') || QMLogLevel::isDebug()){
			self::logMemoryUsage($message);
		}
	}
	/**
	 * @param string|null $message
	 */
	public static function logMemoryUsage(string $message = null){
		$context = [
			//'xdebug_get_function_count' => xdebug_get_function_count(),
			//'xdebug_get_declared_vars' => xdebug_get_declared_vars(), // Usually empty
			//'xdebug_get_headers' => xdebug_get_headers(),
			//'xdebug_peak_memory_usage in MB' => xdebug_peak_memory_usage()/self::MB,  // Returns same as below
			//'xdebug_memory_usage in MB' => xdebug_memory_usage()/self::MB,  // Returns same as below
			'Peak Memory Usage (MB)' => round(memory_get_peak_usage() / self::MB),
			'Current Memory Usage (MB)' => round(memory_get_usage() / self::MB),
			'Available Memory (MB)' => round(self::getAvailableMemory() / self::MB),
		];
		//if(function_exists('xdebug_get_function_count')){$context['xdebug_get_function_count'] = xdebug_get_function_count();}
		if($message){
			QMLog::infoWithoutContext($message);
		}
		QMLog::logKeyValueArray($context, "Memory Usage");
	}
	/**
	 * @return int|null
	 */
	public static function getAvailableMemory(): ?int{
		$limit = ThisComputer::getMemoryLimitInBytes();
		if($limit < 1){
			return null;
		}
		$currentUsage = memory_get_usage();
		$available = $limit - $currentUsage;
		return $available;
	}
	/**
	 * @return int
	 */
	public static function getMemoryLimitInBytes(): int{
		$string = self::getMemoryLimit();
		$string = str_replace([
			                      'M',
			                      'G',
		                      ], [
			                      '000000',
			                      '000000000',
		                      ], $string);
		$int = (int)$string;
		return $int;
	}
	public static function setWorkerMemoryLimit(){
		self::setMemoryLimit(self::WORKER_MEMORY_LIMIT);
		ThisComputer::outputMemoryUsageIfEnabledOrDebug();
	}
	/**
	 * @return bool
	 */
	public static function excessiveMemoryUsage(): bool{
		$memoryUsage = self::getMemoryUsageInMB();
		$limitWithBufferInMB = self::getMemoryLimitWithBufferInMB();
		if($memoryUsage > $limitWithBufferInMB){
			QMLog::error("Memory exceeded ".self::getMemoryLimitWithBufferInMB()." MB");
			return true;
		}
		return false;
	}
	/**
	 * @return float|int
	 */
	public static function getMemoryUsageInMB(): int{
		return round(memory_get_usage() / self::MB);
	}
	/**
	 * @return float
	 */
	public static function getMemoryLimitWithBufferInMB(): float{
		return 0.75 * self::getMemoryLimitInBytes() / self::MB;
	}
	public static function url(string $path): string{
		return "http://".static::getCurrentServerExternalIp().$path;
	}
	/**
	 */
	public static function killPhpUnit(){
		ThisComputer::exec("pkill -f phpunit");
	}
	/**
	 * @param int $numberOfItems
	 * @param int $bytesRequiredPerItem
	 * @throws InsufficientMemoryException
	 */
	public static function exceptionIfInsufficientMemoryForArray(int $numberOfItems, int $bytesRequiredPerItem){
		$limit = ThisComputer::getMemoryLimitInBytes();
		if($limit < 1){
			return;
		}
		$currentUsage = memory_get_usage();
		$available = $limit - $currentUsage;
		$needed = $numberOfItems * $bytesRequiredPerItem;
		if($needed > $available){
			throw new InsufficientMemoryException(                                          "Not enough memory for array with $numberOfItems items each ".
			                                                                                "requiring $bytesRequiredPerItem bytes.",
			                                                                                $needed);
		}
	}
	/**
	 * @param int $bytesRequiredPerItem
	 * @return int|null
	 * @throws InsufficientMemoryException
	 */
	public static function numberOfItemsWeCanHandle(int $bytesRequiredPerItem): ?int{
		$limit = ThisComputer::getMemoryLimitInBytes();
		if($limit < 1){
			return null;
		}
		$currentUsage = memory_get_usage();
		$available = $limit - $currentUsage;
		$items = $available / $bytesRequiredPerItem;
		return $items;
	}
	/**
	 * @return string
	 */
	public static function getBuildConsoleUrl(): ?string{
		if(Env::get(Env::SIMULATE_JENKINS)){
			return Jenkins::JENKINS_URL."/job/Studies-phpunit/7204/console";
		}
		if(Env::get('GITHUB_RUN_ID')){
			return self::githubActionRunUrl();
		}
		if($id = Env::get('BUILD_ID')){
			return "https://console.cloud.google.com/cloud-build/builds;region=global/$id?project=curedao";
		}
		$url = self::getBuildUrl();
		if(!$url){
			return null;
		}
		$url = $url.'/console';
		/** @noinspection SpellCheckingInspection */
		$url = str_replace('consoleconsole', 'console', $url);
		return $url;
	}
	/**
	 * @return string
	 */
	public static function getBuildUrl(): ?string{
		if(Env::get('GITHUB_RUN_ID')){
			return self::githubActionRunUrl();
		}
		if($id = Env::get('BUILD_ID')){
			return "https://console.cloud.google.com/cloud-build/builds;region=global/$id?project=curedao";
		}
		$url = Env::get('BUILD_URL');
		return $url;
	}
	/**
	 * @return array
	 */
	public static function getCommandLineArguments(): array{
		$args = [];
		if(isset($_SERVER["argv"])){
			$args = array_merge($args, $_SERVER["argv"]);
		}
		if(isset($GLOBALS["argv"])){
			$args = array_merge($args, $GLOBALS["argv"]);
		}
		return $args;
	}
	/**
	 * @return string|null
	 */
	public static function PWD(): ?string{
		if(isset($GLOBALS["_SERVER"]["PWD"])){
			return $GLOBALS["_SERVER"]["PWD"];
		}
		if(isset($_SERVER["PWD"])){
			return $_SERVER["PWD"];
		}
		return substr($_SERVER["PHP_SELF"], 0, strrpos( $_SERVER["PHP_SELF"], '/'));
	}
	public static function validatePHPVersion(){
        return;
		$required = self::PHP_VERSION;
		$current = phpversion();
		if(!in_array($current, [
			$required,
			"7.4.23",
			"7.4.24",
			"7.4.25",
            "7.4.30",
		])){ // I can't figure out how to downgrade my
			// local 7
			//.4.23
			throw new RuntimeException(gethostname()." currently has $current active. \n
            To change this version run sudo update-alternatives --config php
            If php $required is not available, you can install it by running
            curl https://gist.githubusercontent.com/mikepsinn/f4d11ef930e62586bf24f15955bd44ea/raw/settler-provision.sh
             We require php $required for consistency.\n
            Inconsistency can cause many nightmares.\n
             If you need to upgrade, be sure to update: \n
                - configs/nginx/shared.qm.nginx.conf \n
                - composer.json => require.php \n
                - composer.json => config.platform.php \n
                - laradock/laradock.env:41 \n
                - Run sudo update-alternatives --config php on all servers \n
                - Rebuild all docker containers \n
                - configs/homestead/after.sh:33 \n
                - .gitlab-ci.yml:1 \n
                - configs/nginx/sites-development/xhgui.nginx.conf:12 \n
                - configs/xhgui/tideways_install.sh:10\n
                - scripts/provision/tideways/xhgui_tideways_install.sh:12 \n
                - scripts/php_restart_site_monitor.sh:76 \n
                - configs/homestead/Homestead.yaml:20 \n
                - production servers");
		}
	}
	/**
	 * @param int $seconds
	 */
	public static function setMaximumPhpExecutionTimeLimit(int $seconds, bool $log = true){
		if($log){
			ConsoleLog::info("Setting maximum execution time limit to $seconds seconds");
		}
		set_time_limit($seconds);
		$gotten = ini_get('max_execution_time');
		if($gotten != $seconds){
			ConsoleLog::error("Failed to set maximum execution time limit to $seconds seconds. Got $gotten instead.");
        }

	}
	/**
	 * @param string $all
	 * @return string
	 */
	public static function addServerBranchJobTestNameDebugHtml(string $all): string{
		$debug = '';
		$arr = self::getServerBranchJobTestNameDebugArray();
		foreach($arr as $key => $value){
			$debug .= "
<p style='font-size: 8px;'>
    $key: $value
</p>
";
		}
		if(stripos($all, $debug) === false){
			$all = HtmlHelper::addDebugHtml($all, $debug);
		}
		return $all;
	}
	/**
	 * @return array
	 */
	public static function getServerBranchJobTestNameDebugArray(): array{
		$arr = [];
		$arr["Server"] = ThisComputer::instance()->getHost();
		if($test = \App\Utils\AppMode::getCurrentTestName()){
			$arr["Test"] = $test;
		}
		try {
			if($branch = QMAPIRepo::getBranchFromMemory()){
				$arr["Branch"] = $branch;
			}
		} catch (NotFoundException $e) {
		}
		if($job = JobTestCase::getJobName()){
			$arr["Job"] = $job;
		}
		return $arr;
	}
	/**
	 * @return string
	 */
	public function getHost(): string{
		$node = Env::get('NODE_NAME');
		if($node){
			return $node;
		}
		return gethostname();
	}
	/**
	 * @return ThisComputer
	 */
	public static function instance(): ThisComputer{
		if(isset(self::$instance)){
			return self::$instance;
		}
		return self::$instance = new static();
	}
	/**
	 * @param string $cmd
	 * @param array $env
	 * @return Process
	 */
	public static function execInIsolation(string $cmd, array $env = []): Process{
		foreach($_ENV as $key => $value){
			$env[$key] = false;
		}
		QMLog::immediately("Running: ".$cmd);
		//fwrite(STDIN, $cmd);
		$process = Process::fromShellCommandline($cmd, FileHelper::absPath(), $env, null, 3600);
		$process->run(function($type, $buffer){
			if(Process::ERR === $type){
				//QMLog::error('Process::ERR > '.$buffer);
				fwrite(STDERR, $buffer);
			} else{
				//\App\Logging\ConsoleLog::info($buffer);
				fwrite(STDOUT, $buffer);
			}
		});
		return $process;
	}
	public static function setPrecision(){
		ini_set('precision', self::PRECISION);
		$p = ini_get('precision');
		if($p !== self::PRECISION){
			le("Could not set precision to ".self::PRECISION.". It is: $p");
		}
		ini_set('serialize_precision', self::SERIALIZE_PRECISION);
		self::validatePrecision();
	}
	public static function validatePrecision(){
		$p = ini_get('precision');
		if($p !== self::PRECISION){
			le("ini_get('precision') should be ".self::PRECISION." but it is: $p");
		}
		$p = ini_get('serialize_precision');
		if($p !== self::SERIALIZE_PRECISION){
			le("ini_get('serialize_precision') should be ".self::SERIALIZE_PRECISION." but it is: $p");
		}
	}
	/**
	 * @return string
	 */
	public static function getMemoryUsageString(): string{
		return "Memory Usage: ".self::getCurrentMemoryUsageMB()." MB";
	}
	/**
	 * @return int
	 */
	public static function getCurrentMemoryUsageMB(): int{
		return round(self::getCurrentMemoryUsage() / 1024 / 1024);
	}
	/**
	 * @return int
	 */
	public static function getCurrentMemoryUsage(): int{
		return memory_get_usage();
	}
	/**
	 * @param string $dir
	 * @return string
	 */
	public static function listDirectoryPermissions(string $dir): string{
		return ThisComputer::exec("ls –ld $dir", "Directory permissions are:");
	}
	/**
	 * @return string
	 */
	public static function outputUser(): string{
		return ThisComputer::echo("Current bash user is \$USER");
	}
	/**
	 * @param string $cmd
	 * @return string
	 */
	public static function echo(string $cmd): string{
		//$output = self::execute("OUTPUT=$($cmd) && echo \"\$cmd OUTPUT: \${OUTPUT}\"");
		$output = self::exec("echo \"$cmd\"");
		//if($title){$output = $title."\n".$output;}
		QMLog::info($output);
		return $output;
	}
	/**
	 * @param string $srcFolder
	 * @param string $destFolder
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public static function copyFolder(string $srcFolder, string $destFolder){
		$fs = new Filesystem();
		$fs->mkdir($destFolder, "0755");
		(new ThisComputer)->chown($destFolder, self::instance()->getUser());
		$srcFolder = FileHelper::absPath($srcFolder);
		self::mirrorDirectory($srcFolder, $destFolder, null, []);
		//self::execute("cp -R $srcFolder/* $destFolder/", "Copying $srcFolder to $destFolder");
	}
	public function getUser(): string{
		if(isset($_SERVER["USER"])){return $_SERVER["USER"];}
		if($u = get_current_user()){return $u;}
		$u = trim(QMStr::stripNewLines(self::echo("\$USER")));
		$u = QMStr::between("$u", '"$USER"', 'Exit');
		$u = trim($u);
		//QMLog::info("Linux USER is $u");
		return $u;
	}
	/**
	 * Mirrors a directory to another.
	 * Copies files and directories from the origin directory into the target directory. By default:
	 *  - existing files in the target directory will be overwritten, except if they are newer (see the `override`
	 * option)
	 *  - files in the target directory that do not exist in the source directory will not be deleted (see the `delete`
	 * option)
	 * @param \Traversable|null $iterator Iterator that filters which files and directories to copy, if null a
	 *     recursive iterator is created
	 * @param array $options An array of boolean options
	 *                                    Valid options are:
	 *                                    - $options['override'] If true, target files newer than origin files are
	 *     overwritten (see copy(), defaults to false)
	 *                                    - $options['copy_on_windows'] Whether to copy files instead of links on
	 *     Windows (see symlink(), defaults to false)
	 *                                    - $options['delete'] Whether to delete files that are not in the source
	 *     directory (defaults to false)
	 * @throws IOException When file type is unknown
	 */
	public static function mirrorDirectory(string $originDir, string $targetDir, \Traversable $iterator = null,
	                                       array  $options = []){
		$fs = new Filesystem();
		$fs->mirror($originDir, $targetDir, $iterator, $options);
	}
	/**
	 * @param string $folder
	 * @param string|null $user
	 * @param string|null $group
	 * @param string $filePerm
	 * @param string $folderPerm
	 */
	public static function setFolderOwnerAndPermissions(string $folder, string $user = null, string $group = null,
	                                                    string $filePerm = FileHelper::DEFAULT_FILE_PERMISSIONS, string $folderPerm =
		FileHelper::DEFAULT_FOLDER_PERMISSIONS){
		if(!$user){
			$user = self::instance()->getUser();
		}
		if(!$group){
			$group = $user;
		}
		$folder = FileHelper::absPath($folder);
		self::exec("cd $folder && sudo find . -exec chown $user:$group {} \; &&
sudo find . -type d -exec chmod $folderPerm {} \; &&
sudo find . -type f -exec chmod $filePerm {} \;");
	}
	/**
	 * @param string $dir
	 * @param string $owner
	 * @param string|null $group
	 * @return void
	 */
	public static function ownDirectory(string $dir, string $owner, string $group = null): void{
		$dir = abs_path($dir);
		if(!$group){$group = $owner;}
		self::sudoStatic("chown -R $owner:$group $dir");
	}

    /**
     * @param string $absPath
     * @param string $owner
     * @param string|null $group
     * @return void
     * @throws FilePermissionsException
     */
	public static function ownFile(string $absPath, string $owner, string $group = null): void{
		$absPath = abs_path($absPath);
		if(!$group){
			$group = FileHelper::getDefaultGroupName();
		}
		self::sudoStatic("chown $owner::$group $absPath");
	}
	/**
	 * @param string $src
	 * @param string $destination
	 * @return void
	 */
	public static function sudoCopy(string $src, string $destination): void{
		$destination = abs_path($destination);
		self::sudoStatic("cp $src $destination");
	}
	public static function logDebugUrlsForCurrentComputer(){
		self::instance()->logDebugUrls();
	}
	/**
	 * @return array
	 */
	protected static function getNodeLabels(): array{
		$str = Env::get("NODE_LABELS");
		if(!$str){
			ConsoleLog::info("Could not get node labels from NODE_LABELS environment variable");
			return [];
		}
		$arr = explode(" ", $str);
		foreach($arr as $v){
			$exploded = explode("=", $v);
			$arr[$exploded[0]] = $exploded[1] ?? 1;
		}
		return $arr;
	}
	public static function logAaPanelUrl(){
		$url = self::getAAPanelUrl();
		QMLog::linkButton("AAPanel", $url);
	}
	public static function logUrls(){
		static::logAaPanelUrl();
		QMLog::linkButton("Clockwork", static::getClockworkUrl());
	}
	/**
	 * @param string $name
	 * @return string
	 */
	public static function getNodeLabel(string $name): ?string{
		return self::getNodeLabels()[$name] ?? null;
	}

    /**
     * @throws NoInternetException
     */
    public static function getAAPanelUrl(string $ip = null): string{
        if(!$ip){$ip = static::getRunnerIP($ip);}
		if(!$ip){$ip = static::getCurrentServerExternalIp();}
		/** @noinspection HttpUrlsUsage */
		return JenkinsSlave::getAAPanelUrl($ip);
	}
	/**
	 * @return string
	 * @throws NoInternetException
	 */
	public static function getClockworkUrl(): string{
		$ip = static::getCurrentServerExternalIp();
		/** @noinspection HttpUrlsUsage */
		return "http://$ip/__clockwork/app#";
	}

    public static function npm(string $cmd, bool $log = false, bool $obfuscate = true)
    {
        $bin = '/www/server/nodejs/v16.17.0/bin';
        if(!FileHelper::folder_exist($bin)){
            $bin = ' /usr/local/bin';
        }
        $npm = $bin.'/npm';
        return self::exec("$npm $cmd", $log, $obfuscate);
    }

    public static function home()
    {
	    $result = $_SERVER['HOME'] ?? getenv("HOME");

	    if(empty($result) && function_exists('exec')) {
		    if(strncasecmp(PHP_OS, 'WIN', 3) === 0) {
			    $result = exec("echo %userprofile%");
		    } else {
			    $result = exec("echo ~");
		    }
	    }

	    return $result;
    }
	/**
	 * @return string
	 */
	private static function githubActionRunUrl(): string{
		return Env::get('GITHUB_SERVER_URL')."/".Env::get('GITHUB_REPOSITORY')."/actions/runs/".
		       Env::get('GITHUB_RUN_ID');
	}
	public static function getComputerName(){
		if($name = static::getRunnerName()){
			return $name;
		}
		if($name = Env::get(JenkinsAPI::NODE_NAME)){
			return $name;
		}
		return gethostname();
	}
	public function getQmApiAbsPath(): string{ return abs_path(); }
	public function getQmApiPath(): string{ return abs_path(); }
	/**
	 * @param string $string
	 * @throws CommandFailureException
	 */
	public function artisan(string $string){
		try {
			parent::artisan($string);
		} catch (OfflineException $e) {
			le($e);
		}
	}
	public function test(): bool{
		$shell = new Exec();
		$command = new Builder('phpunit');
		$command->addFlag('v')->addArgument('stop-on-failure')->addArgument('configuration', '~/phpunit.xml')
		        ->addParam('~/tests/TestCase.php');
		$shell->run($command);
		return $shell->getReturnValue() === 0;
	}
	/**
	 */
	public function restartServices(){
		ThisComputer::exec("bash scripts/services.sh");
	}
	/**
	 * @return int
	 */
	public function getPort(): int{
		if($this->port){
			return $this->port;
		}
		$ssh = Env::get('SSH_CONNECTION');
		if(!empty($ssh)){
			$this->port = explode(" ", $ssh)[3];
		}
		if(!$this->port){
			return 22;
		}
		return $this->port;
	}
	/**
	 * @param array|string $command
	 * @return Process
	 */
	public function execute($command): Process{
		return static::exec($command)->process();
	}
	/**
	 * @return string
	 */
	public function getUrl(): string{
		if(AppMode::isJenkins()){
			return parent::getUrl();
		}
		if(AppMode::isUnitTest()){
			return UrlHelper::getTestUrl();
		}
		return UrlHelper::getLocalUrl();
	}
	/**
	 * @return string
	 * @throws \App\Exceptions\NoInternetException
	 */
	public function getIP(): string{
		if(EnvOverride::isLocal()){
			return "127.0.0.1";
		}
		return $this->ip = ThisComputer::getCurrentServerExternalIp();
		//return parent::getIP();
	}
	/**
	 * Get an instance of the Process builder class.
	 *
	 * @return \TitasGailius\Terminal\Builder
	 */
	public static function builder(): \TitasGailius\Terminal\Builder{
		return new \TitasGailius\Terminal\Builder;
	}
	/**
	 * Dynamically pass method calls to a new Builder instance.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return \TitasGailius\Terminal\Builder
	 */
	public static function __callStatic(string $method, array $parameters)
	{
		return call_user_func([static::builder(), $method], ...$parameters);
	}
	public static function isWebUser(): bool{
        $u = self::instance()->getUser();
		return in_array($u, [self::USER_WWW, self::USER_WWW_DATA]);
    }
	public static function isRoot(): bool{
		$u = self::instance()->getUser();
		return $u === self::USER_ROOT;
	}
	public static function getHostAddress(): string {
		$url = AppMode::isDocker() ? 'host.docker.internal' : '127.0.0.1';
		//debugger($url);
		return $url;
	}
}
