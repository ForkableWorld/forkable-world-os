<?php /** @noinspection PhpUnused */ /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpUnusedParameterInspection */
/** @noinspection PhpDeprecationInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace App\Repos;
use App\Buttons\Admin\GithubBranchButton;
use App\Buttons\Admin\GithubCommitButton;
use App\Buttons\Admin\JenkinsConsoleButton;
use App\Buttons\QMButton;
use App\Computers\ThisComputer;
use App\DataSources\Connectors\Responses\Github\Owner;
use App\DevOps\ComposerJson;
use App\DevOps\Jenkins\JenkinsAPI;
use App\DevOps\Jenkins\JenkinsJob;
use App\DevOps\Jenkins\JenkinsQueue;
use App\DevOps\PackageJson;
use App\Exceptions\GitAlreadyUpToDateException;
use App\Exceptions\GitBranchAlreadyExistsException;
use App\Exceptions\GitBranchNotFoundException;
use App\Exceptions\GitConflictException;
use App\Exceptions\GitLockException;
use App\Exceptions\GitNoStashException;
use App\Exceptions\GitRepoAlreadyExistsException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\NoFileChangesException;
use App\Exceptions\NotFoundException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Files\PHP\PhpClassFile;
use App\Folders\AppReposFolder;
use App\Folders\ReposFolder;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\User;
use App\Notifications\LinkNotification;
use App\PhpUnitJobs\JobTestCase;
use App\Slim\View\Request\QMRequest;
use App\Storage\LocalFileCache;
use App\Storage\S3\S3Public;
use App\Traits\HasClassName;
use App\Traits\LoggerTrait;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\FontAwesome;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\EnvOverride;
use App\Utils\SecretHelper;
use Github\Api\RateLimit;
use Github\Api\RateLimit\RateLimitResource;
use Github\Api\Repo;
use Github\Api\Repository\Checks\CheckRuns;
use Github\Api\Repository\Comments;
use Github\Api\Repository\Contents;
use Github\Api\Repository\Statuses;
use Github\Api\Search;
use Github\AuthMethod;
use Github\Client;
use Github\Exception\MissingArgumentException;
use Github\Exception\RuntimeException;
use Github\HttpClient\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use TitasGailius\Terminal\Terminal;
/** Git Repository Interface Class
 * This class enables the creating, reading, and manipulation
 * of a git repository
 */
class GitRepo {
	use HasClassName, LoggerTrait;
	const BRANCH_DEVELOP = 'develop';
	const BRANCH_MASTER = 'master';
	public const COMMITTER = [
		'name' => 'Mike P. Sinn',
		'email' => 'm@quantimodo.com',
	];
	public const DEFAULT_BRANCH = 'master';
	public const LOCAL_TO_S3_PATH_MAP = [];
	public const PUBLIC = true;
	public const RELATIVE_PATH = null;
	public static $REPO_NAME = null;
	public const S3_PATH = null;
	public const STATE_error = 'error';
	public const STATE_failure = 'failure';
	public const STATE_pending = 'pending';
	public const STATE_success = 'success';
	public const USERNAME = 'mikepsinn';
	public static $bin = '/usr/bin/git';
	public static $reposPulled = []; // Git executable location
	protected static $commitSha;
	protected static $branchByRepo;
	protected static $oldFiles;
	/**
	 * @var string
	 */
	protected static $message;
	/**
	 * @param bool $overwrite
	 * @return string[]
	 */
	public static function uploadToS3Public(bool $overwrite = false): array{
		static::cloneIfNecessary();
		$urls = [];
		foreach(static::LOCAL_TO_S3_PATH_MAP as $local => $remote){
			$local = static::getAbsolutePath($local);
			$urls = array_merge($urls,
				S3Public::uploadFolder($local, $remote, $overwrite, true, PublicRepo::excludeNamesLike()));
		}
		return $urls;
	}
	public static function cloneIfNecessary(){
		$repoPath = static::getAbsPath();
		//$absPath = FileHelper::folder_exist($repoPath);
		$gitFolder = FileHelper::folder_exist($repoPath . "/.git");
		if($gitFolder){
			return;
		}
		$gitFolder = FileHelper::fileExists($repoPath . "/.git");
		if($gitFolder){
			return;
		}  // application-settings is a submodule and has a .git file instead of folder
		static::clonePullAndOrUpdateRepo();
	}
	/**
	 * @return string
	 */
	public static function getAbsPath(): string{
		return ReposFolder::generateAbsPath(static::getRepoName());
	}
	/**
	 * @param string|null $username
	 * @param string|null $repoName
	 * @return array
	 */
	public static function getRepoInfoFromGithubAPI(?string $username = null, ?string $repoName = null): array{
		$api = static::repoClient();
		if(!$username){$username = static::getOwnerName();}
		if(!$repoName){$repoName = static::getRepoName();}
		$data = $api->show($username, $repoName);
		return $data;
	}
	/**
	 * @return \CzProject\GitPhp\GitRepository
	 */
	public static function gitPHP(): \CzProject\GitPhp\GitRepository{
		$git = new \CzProject\GitPhp\Git;
		// create repo object
		$repo = $git->open(static::getAbsPath());
		return $repo;
	}
	/**
	 * @return string
	 */
	public static function getRelativePath(): string{
		return ReposFolder::generateRelativePath(static::getUserName(), static::getRepoName());
	}
	/**
	 * @return string
	 */
	public static function getUserName(): string{
		return static::USERNAME;
	}
	/**
	 * @return string
	 */
	public static function getRepoName(): string{
		if(!static::$REPO_NAME){
			if(!static::$REPO_NAME){
				le("no repo name for " . static::class);
			}
		}
		return static::$REPO_NAME;
	}
	public static function clonePullAndOrUpdateRepo(){
		$name = static::getRepoName();
		$process = "UPDATING $name";
		QMLog::logStartOfProcess($process);
		static::logWithGithubUrl(__FUNCTION__);
		if(in_array($name, static::$reposPulled)){
			ConsoleLog::info("Already pulled $name");
			return;
		}
		if(!static::alreadyCloned()){
			static::cloneRepo();
		} else{
			static::stash();
			static::hardReset();
			try {
				static::pull(static::DEFAULT_BRANCH);
			} catch (\Throwable $e) {
				ConsoleLog::error("Have to do fetchForceCheckoutAndPull because: " . $e->getMessage());
				static::fetchForceCheckoutAndPull();
			}
		}
		static::postUpdate();
		QMLog::logEndOfProcess($process);
	}
	public static function logWithGithubUrl(string $message){
		static::logWithRepoName($message . "\n" . static::getGithubUrl());
	}
	public static function logWithRepoName(string $message){
		ConsoleLog::info(static::getRepoName() . ": " . $message);
	}
	public static function getGithubUrl(): string{
		$ownerRepo = static::getOwnerRepo();
		return "https://github.com/$ownerRepo";
	}
	public static function getOwnerRepo(): string{
		return static::USERNAME . "/" . static::getRepoName();
	}
	private static function alreadyCloned(): bool{
		return static::fileExists(".git");
	}
	public static function fileExists(string $filePath): bool{
		$path = static::getAbsolutePath($filePath);
		return FileHelper::fileExists($path);
	}
	/**
	 * @param string $relativePath
	 * @return string
	 */
	public static function getAbsolutePath(string $relativePath = ""): string{
		$repo = static::getAbsPath();
		if(empty($relativePath)){
			return $repo;
		}
		if(str_starts_with($relativePath, $repo)){
			return $relativePath;
		}
		$path = $repo . DIRECTORY_SEPARATOR . $relativePath;
		return str_replace('//', DIRECTORY_SEPARATOR, $path);
	}
	/**
	 * @param string|null $repoPath
	 * @param string|null $branch
	 * @return string
	 */
	public static function cloneRepo(string $repoPath = null, string $branch = null): string{
		if(!$repoPath){
			$repoPath = static::getAbsPath();
		}
		FileHelper::createDirectoryIfNecessary($repoPath);
		if(!$branch){
			$branch = static::DEFAULT_BRANCH;
		}
		$source = static::getRemoteUrlWithToken();
		static::$reposPulled[] = static::getRepoName();
		// Don't use GitRepo execute because we can't `cd` into directory we haven't cloned yet
		$res = ThisComputer::exec("git fetch $source",
			true);
		try {
			$res = ThisComputer::exec("git clone --recursive -b $branch $source $repoPath",
			                          true);
		} catch (\Throwable $e) {
		    if(str_contains($e->getMessage(), "already exists and is not an empty directory")){
		        ConsoleLog::info("Already cloned " . static::getRepoName(). " into $repoPath. Will try to pull");
				$builder = Terminal::builder();
				$builder->in($repoPath);
				$response = $builder->run("git pull", ConsoleLog::logTerminalOutput());
			    $output = $response->output();
			    return $output;
			}
		}
		if(stripos($res, "fatal:") !== false){
			static::throwException($res, $res);
		}
		return $res;
	}
	/**
	 * @return string
	 */
	public static function getRemoteUrlWithToken(): string{
		return 'https://' . \App\Utils\Env::get('GITHUB_ACCESS_TOKEN') . '@github.com/' . static::USERNAME . '/' .
			static::getRepoName();
	}
	/**
	 * @param $stderr
	 * @param $stdout
	 * @param string|null $cmd
	 * @throws GitAlreadyUpToDateException
	 * @throws GitBranchAlreadyExistsException
	 * @throws GitBranchNotFoundException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws GitRepoAlreadyExistsException
	 */
	private static function throwException($stderr, $stdout, string $cmd = null): void{
		$str = $cmd . "\n" . $stderr . "\n" . $stdout;
		$str = SecretHelper::obfuscateString($str);
		if(stripos($str, 'already exists') !== false){
			if(stripos($str, 'A branch named ') !== false){
				throw new GitBranchAlreadyExistsException($str);
			}
			throw new GitRepoAlreadyExistsException($str);
		}
		if(stripos($str, 'index.lock') !== false){
			throw new GitLockException($str);
		}
		if(stripos($str, 'up to date') !== false){
			throw new GitAlreadyUpToDateException($str);
		}
		if(str_contains($str, 'CONFLICT ')){
			throw new GitConflictException($str);
		}
		if(stripos($str, 'No stash found') !== false){
			throw new GitNoStashException($str);
		}
		if(stripos($str, 'remote ref does not exist') !== false){
			throw new GitBranchNotFoundException($str);
		}
		if(stripos($str, 'The following paths are ignored by one of your .gitignore files') !== false){
			throw new GitBranchNotFoundException($str);
		}
		if(stripos($str, "fatal: Couldn't find remote ref") !== false){
			throw new GitBranchNotFoundException($str);
		}
		le($str);
	}
	/**
	 * @param string|null $branch
	 * @param string $remote
	 * @return string
	 * @throws GitAlreadyUpToDateException
	 * @throws GitBranchAlreadyExistsException
	 * @throws GitBranchNotFoundException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws GitRepoAlreadyExistsException
	 */
	public static function hardReset(string $branch = null, string $remote = "origin"): string{
		if(!$branch){
			$branch = static::DEFAULT_BRANCH;
		}
		//unlink(FileHelper::getAbsolutePathFromRelative('tmp/qm-studies/.git/index.lock'));
		return static::runCommand("reset --hard $remote/$branch");
	}
	/**
	 * Run a git command in the git repository
	 * Accepts a git command to run
	 * @access  public
	 * @param string $command command to run
	 * @param bool $outputToConsole
	 * @return  string
	 * @throws GitAlreadyUpToDateException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws GitRepoAlreadyExistsException
	 * @throws GitBranchAlreadyExistsException
	 * @throws GitBranchNotFoundException
	 */
	protected static function runCommand(string $command, bool $outputToConsole = true): string{
		if(AppMode::isApiRequest()){
			QMLog::error("Should we be running $command commands during an API request?");
		}
		$path = static::getAbsPath();
		$repoName = static::getRepoName();
		if(static::class !== QMAPIRepo::class){
			$pathContainsRepoName = str_contains($path, $repoName);
			$containsPublic = str_contains($path, "public");
			if(!$pathContainsRepoName){
				if(!$containsPublic){
					le("wrong path: $path! It should contain public or the repo name: $repoName.\n\t".
					   "strpos($path, $repoName) is ".\App\Logging\QMLog::print_r(strpos($path, $repoName), true));
				}
			}
        }
		$token = \App\Utils\Env::get('GITHUB_ACCESS_TOKEN');
		if(empty($token)){
			le("GITHUB_ACCESS_TOKEN not set!");
		}
		$secured = str_replace($token, '[secure]', $command);
		if($outputToConsole){
			QMLog::infoWithoutContext(static::getRepoName() . ": git $secured");
		}
		$home = \App\Utils\Env::get('HOME');
		if(empty($home)){
			$home = "/home/vagrant";
		}
		if(!static::fileExists(".git")){
			le("!fileExists(.git)");
		}
        $bin = static::getGitBinPath();
        if(AppMode::isWindows()){
            $command = "cd $path && \"$bin\" " . $command;
        } else {
            $command = "cd $path && export HOME=$home && $bin " . $command;
        }

        //$command = "cd $path && git " . $command;
		//ConsoleLog::info($command);
		$pipes = [];
		$resource = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w'],], $pipes, $path, $_ENV);
		$stdout = stream_get_contents($pipes[1]); //Not all errors are printed to stderr, so include std out as well.
		$stderr = stream_get_contents($pipes[2]);
		foreach($pipes as $pipe){
			fclose($pipe);
		}
		$status = trim(proc_close($resource));
		if($status){
			static::throwException($stderr, $stdout, $command);
		}
		if($outputToConsole){
			QMLog::infoWithoutContext($stdout, true);
		}
		return $stdout;
	}
	/**
	 * @param string|null $branch
	 */
	public static function fetchForceCheckoutAndPull(string $branch = null){
		ConsoleLog::info(__FUNCTION__ . " " . static::getRepoName());
		// What is this for? It causes The following paths are ignored by one of your .gitignore files exception static::add("*");
		try {
			static::stash();
		} catch (\Throwable $e) {
			if(str_contains($e->getMessage(), "is a directory")){
				QMLog::info(__METHOD__.": ".$e->getMessage());
			} else{
				throw $e;
			}
		}
		static::fetch();
		static::checkoutForce($branch);
		static::pull($branch);
	}
	/**
	 * @param string $opts
	 * @return string
	 * @throws GitAlreadyUpToDateException
	 * @throws GitBranchAlreadyExistsException
	 * @throws GitBranchNotFoundException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws GitRepoAlreadyExistsException
	 */
	public static function stash(string $opts = "--include-untracked"): string{
		return static::runCommand("stash $opts");
	}
	/**
	 * Runs a git fetch on the current branch
	 * @access  public
	 * @return  string
	 */
	public static function fetch(): string{
		return static::runCommand("fetch");
	}
	/**
	 * Runs a git fetch on the current branch
	 * @access  public
	 * @param string|null $branch
	 * @return  string
	 * @throws GitAlreadyUpToDateException
	 * @throws GitBranchAlreadyExistsException
	 * @throws GitBranchNotFoundException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws GitRepoAlreadyExistsException
	 */
	public static function checkoutForce(string $branch = null): string{
		if(!$branch){
			$branch = static::DEFAULT_BRANCH;
		}
		return static::runCommand("checkout --force $branch");
	}
	/**
	 * Pull specific branch from remote
	 * Accepts the name of the remote and local branch.
	 * If omitted, the command will be "git pull", and therefore will take on the
	 * behavior as-configured in your clone / environment.
	 * @param string|null $branch
	 * @param string $remote
	 * @return string
	 * @throws GitAlreadyUpToDateException
	 * @throws GitBranchAlreadyExistsException
	 * @throws GitBranchNotFoundException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws GitRepoAlreadyExistsException
	 */
	public static function pull(string $branch = null, string $remote = "origin"): string{
		if(!$branch){
			$branch = static::getBranchFromGit();
		}
		static::$reposPulled[] = static::getRepoName();
		return static::runCommand("pull $remote $branch");
	}
	/**
	 * Returns name of active branch
	 * @access  public
	 * @param bool $keep_asterisk
	 * @param bool $outputToConsole
	 * @return  string
	 */
	public static function getBranchFromGit(bool $keep_asterisk = false, bool $outputToConsole = false): string{
		$phpGit = static::phpGitRepo();
		$active_branch = $phpGit->getCurrentBranchName();
		return static::$branchByRepo[static::class] = $active_branch;
	}
	/**
	 * Runs a `git branch` call
	 * @param bool $keep_asterisk
	 * @param bool $outputToConsole
	 * @return  array
	 */
	public static function list_local_branches(bool $keep_asterisk = false, bool $outputToConsole = false): array{
		$output = static::runCommand("branch", $outputToConsole);
		return static::branchOutputToArray($output, $keep_asterisk);
	}
	/**
	 * @param string $output
	 * @param bool $keep_asterisk
	 * @return string[]
	 */
	private static function branchOutputToArray(string $output, bool $keep_asterisk){
		$branchArray = explode("\n", $output);
		foreach($branchArray as $i => &$branch){
			$branch = trim($branch);
			if(!$keep_asterisk){
				$branch = str_replace("* ", "", $branch);
			}
			if($branch === ""){
				unset($branchArray[$i]);
			}
		}
		return $branchArray;
	}
	/**
	 * @return string|null
	 */
	public static function getCommitDate(): ?string{
		$path = static::getAbsPath();
		$response = ThisComputer::terminalRun("cd $path && git log -n1 --pretty=%ci HEAD");
		$out = $response->output();
		$lines = $response->lines();
		return db_date(QMStr::removeNewLines($out));
	}
	/**
	 * Runs a `git rm` call
	 * Accepts a list of files to remove
	 * @access  public
	 * @param string|array $files
	 * @param Boolean $cached use the --cached flag?
	 * @return  string
	 */
	public static function rm($files, bool $cached): string{
		if(is_array($files)){
			$files = '"' . implode('" "', $files) . '"';
		}
		return static::runCommand("rm " . ($cached ? '--cached ' : '') . $files);
	}
	/**
	 * Runs a `git clean` call
	 * Accepts a remove directories flag
	 * @access  public
	 * @param bool $dirs
	 * @param bool $force
	 * @return  string
	 */
	public static function clean(bool $dirs, bool $force): string{
		return static::runCommand("clean" . ($force ? " -f" : "") . ($dirs ? " -d" : ""));
	}
	/**
	 * Runs a `git merge` call
	 * Accepts a name for the branch to be merged
	 * @access  public
	 * @param string $branch
	 * @return  string
	 */
	public static function mergeNoFastForward(string $branch): string{
		return static::runCommand("merge " . escapeshellarg($branch) . " --no-ff");
	}
	/**
	 * Runs a git fetch on the current branch
	 * @access  public
	 * @return  string
	 */
	public static function fetchAll(): string{
		return static::runCommand("fetch --all");
	}
	public static function cloneAndPullIfNotLocal(){
		$process = "UPDATING " . static::getRepoName();
		QMLog::logStartOfProcess($process);
		static::cloneIfNecessary();
		if(!EnvOverride::isLocal()){
			static::fetchForceCheckoutAndPull();
		}
		QMLog::logEndOfProcess($process);
	}
	/**
	 * Push specific branch (or all branches) to a remote
	 * Accepts the name of the remote and local branch.
	 * If omitted, the command will be "git push", and therefore will take
	 * on the behavior of your "push.default" configuration setting.
	 * @param string $message
	 * @param string|null $branch
	 * @param string $remote
	 * @return string
	 */
	public static function stashPullAddCommitAndPush(string $message, string $branch = null,
		string $remote = "origin"): string{
		static::stashPullAndCommit($message, $branch, $remote);
		return static::push($branch, $remote);
	}
	/**
	 * @param string $message
	 * @param string|null $branch
	 * @param string $remote
	 */
	private static function stashPullAndCommit(string $message, string $branch = null, string $remote = "origin"): void{
		if(!$branch){
			$branch = static::DEFAULT_BRANCH;
		}
		static::stash();
		static::pull($branch, $remote);
		static::runCommand("stash pop");
		static::addAndCommit($message);
	}
	/**
	 * @param string $message
	 * @param string $files
	 */
	public static function addAndCommit(string $message, string $files = "."){
		static::add($files);
		$commitAll = $files === ".";
		static::commit($message, $commitAll);
	}
	/** @noinspection PhpDocMissingThrowsInspection */
	/**
	 * Runs a `git add` call
	 * Accepts a list of files to add
	 * @access  public
	 * @param string|array $files files to add
	 * @return  string
	 */
	public static function add($files = "."): string{
		if(is_array($files)){
			$files = '"' . implode('" "', $files) . '"';
		}
		return static::runCommand("add $files -v");
	}
	/**
	 * Runs a `git commit` call
	 * Accepts a commit message string
	 * @access  public
	 * @param string $message commit message
	 * @param boolean $commit_all should all files be committed automatically (-a flag)
	 * @return  string
	 */
	public static function commit(string $message, bool $commit_all = false): string{
		$flags = $commit_all ? '-av' : '-v';
		// by using the -a switch with the commit command to automatically "add" changes from all known files
		// (i.e. all files that are already listed in the index) and to automatically "rm" files in the index that
		// have been removed from the working tree, and then perform the actual commit;
		// -v --verbose Show unified diff between the HEAD commit and what would be committed at the bottom of the
		// commit message template to help the user describe the commit by reminding what changes the commit has.
		// Note that this diff output doesn’t have its lines prefixed with #. This diff will not be a part of the
		// commit message. See the commit.verbose configuration variable in git-config[1].
		// If specified twice, show in addition the unified diff between what would be committed and the worktree files,
		// i.e. the unstaged changes to tracked files.
		static::checkForBlackListedStrings();
		try {
			return static::runCommand("commit " . $flags . " -m " .
				escapeshellarg($message . static::getCommitDescription()));
		} catch (GitAlreadyUpToDateException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return $e->getMessage();
		} catch (GitConflictException | GitRepoAlreadyExistsException | GitNoStashException | GitLockException $e) {
			return $e->getMessage();
		}
	}
	public static function validate(): void {
		static::checkForBlackListedStrings();
	}
	public static function checkForBlackListedStrings(): void{
		ConsoleLog::info("Checking " . static::getRepoName() . " for secrets...");
		foreach(static::getBlackListedStrings() as $secret){
			if($files = static::searchFiles($secret)){
				foreach($files as $key => $value){
					$files[$key] = $value->getRealPath();
				}
				le("These files contain $secret: ", $files);
			}
		}
	}
	protected static function getBlackListedStrings(array $repoSpecific = []): array{
		$arr = SecretHelper::getSecretValues();
		return array_merge($repoSpecific, $arr);
	}
	/**
	 * @param string $needle
	 * @param bool $recursive
	 * @param string|null $filenameLike
	 * @return SplFileInfo[]
	 */
	public static function searchFiles(string $needle, bool $recursive = true, string $filenameLike = null): array{
		return FileHelper::searchFiles(static::getAbsolutePath(), $needle, $recursive, $filenameLike);
	}
	public static function getCommitDescription(): string{
		$desc = "\nLove, " . (new ThisComputer)->getHost() . " Server";
		if(AppMode::isJenkins()){
			$desc .= "\n" . JenkinsConsoleButton::generateUrl();
			$desc .= "\n" . JobTestCase::getJobName();
		}
		return $desc;
	}
	/**
	 * Push specific branch (or all branches) to a remote
	 * Accepts the name of the remote and local branch.
	 * If omitted, the command will be "git push", and therefore will take
	 * on the behavior of your "push.default" configuration setting.
	 * @param string|null $branch
	 * @param string $remote
	 * @return string
	 */
	public static function push(string $branch = null, string $remote = "origin"): string{
		if(!$branch){
			$branch = static::getBranchFromGit();
		}
		//--tags removed since this was preventing branches from being pushed (only tags were)
		try {
			$res = static::runCommand("push -u $remote $branch");
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			$res = $e->getMessage();
		}
		try {
			static::fetch();
			static::setUpstream($branch);
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			$res .= "\n" . $e->getMessage();
		}
		if($branch !== static::DEFAULT_BRANCH){
			static::notifyOfPullRequest($branch);
		}
		return $res;
	}
	/**
	 * @param string|null $branch
	 * @throws GitAlreadyUpToDateException
	 * @throws GitBranchAlreadyExistsException
	 * @throws GitBranchNotFoundException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws GitRepoAlreadyExistsException
	 */
	protected static function setUpstream(?string $branch): void{
		static::runCommand("branch --set-upstream-to=origin/$branch $branch");
	}
	/**
	 * @param string|null $branch
	 */
	public static function notifyOfPullRequest(string $branch): void{
		$instructions = static::getMergeInstructions($branch);
		User::mike()->notify(new LinkNotification("Pull Request", static::getBranchCompareUrl($branch), $instructions,
			static::getImage()));
		QMLog::importantInfo($instructions);
		QMLog::error($instructions);
	}
	/**
	 * @param string|null $branch
	 * @return string
	 */
	protected static function getMergeInstructions(?string $branch): string{
		return "Merge pull request at " . static::getBranchCompareUrl($branch);
	}
	protected static function getBranchCompareUrl(string $branch, string $baseBranch = null): string{
		if(!$baseBranch){
			$baseBranch = static::DEFAULT_BRANCH;
		}
		$repo = static::getOwnerRepo();
		return "https://github.com/$repo/compare/$baseBranch...$branch?expand=1";
	}
	public static function getImage(): string{
		$data = static::repo();
		return $data->getImage();
	}
	public static function repo(): \App\DataSources\Connectors\Responses\Github\Repo{
		$data = static::repoClient()->show(static::USERNAME, static::getRepoName());
		return new \App\DataSources\Connectors\Responses\Github\Repo($data);
	}
	/**
	 * @return Repo
	 * @noinspection PhpIncompatibleReturnTypeInspection
	 */
	public static function repoClient(): Repo{
		return static::client()->api('repo');
	}
	/**
	 * @return Client
	 */
	public static function client(): Client{
		return static::github();
	}
	/**
	 * @return Client
	 */
	public static function github(): Client{
		$builder = new Builder();
		$builder->addHeaderValue('Accept', 'application/vnd.github.squirrel-girl-preview');
		$github = new Client($builder);
		$token = Env::get('GITHUB_ACCESS_TOKEN');
        if(empty($token)){
            le("Please set GITHUB_ACCESS_TOKEN env from https://github.com/settings/tokens/new");
        }
		$github->authenticate($token, null, AuthMethod::ACCESS_TOKEN);
		return $github;
	}
	/**
	 * Push specific branch (or all branches) to a remote
	 * Accepts the name of the remote and local branch.
	 * If omitted, the command will be "git push", and therefore will take
	 * on the behavior of your "push.default" configuration setting.
	 * @param string $message
	 * @param string|null $branch
	 * @param string $remote
	 * @return string
	 * @throws GitAlreadyUpToDateException
	 * @throws GitBranchAlreadyExistsException
	 * @throws GitBranchNotFoundException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws GitRepoAlreadyExistsException
	 */
	public static function commitAndPush(string $message, string $branch = null, string $remote = "origin"): string{
		if($job = JenkinsJob::getCurrentJobName()){
			if(stripos($message, $job) === false){
				$message .= " | Job: $job";
			}
		}
		static::runCommand("commit -m " . escapeshellarg($message));
		return static::push($branch, $remote);
	}
	/**
	 * List log entries.
	 * @param string|null $format
	 * @param bool $fullDiff
	 * @param null $filepath
	 * @param bool $follow
	 * @return string
	 */
	public static function gitLog(string $format = null, bool $fullDiff = null, $filepath = null,
		bool $follow = null): string{
		$diff = "";
		if($fullDiff){
			$diff = "--full-diff -p ";
		}
		if($follow){
			// Can't use full-diff with follow
			$diff = "--follow -- ";
		}
		if($format === null){
			return static::runCommand('log ' . $diff . $filepath);
		}
		return static::runCommand('log --pretty=format:"' . $format . '" ' . $diff . $filepath);
	}
	public static function stashAndReset(string $branch = null, string $remote = "origin"){
		QMLog::info(static::getRepoName() . ": " . __FUNCTION__);
		static::fetchForceCheckoutAndPull($branch);
	}
	/**
	 * @param string $message
	 * @param string $branch
	 * @param string $remote
	 */
	public static function stashSwitchBranchCommitPush(string $message, string $branch,
		string $remote = "origin"): void{
		static::stashBranchPop($branch, $remote);
		static::addCommitPush($message, $branch);
	}
	/**
	 * @param string $branch
	 * @param string $remote
	 */
	protected static function stashBranchPop(string $branch, string $remote = "origin"): void{
		static::stash();
		try {
			static::createBranch($branch);
		} catch (GitBranchAlreadyExistsException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		static::checkout($branch);
		try {
			static::pull($branch, $remote);
		} catch (Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		static::runCommand("stash pop");
	}
	/**
	 * @param string $branch
	 * @param string|null $branchFrom
	 * @return string
	 * @throws GitAlreadyUpToDateException
	 * @throws GitBranchAlreadyExistsException
	 * @throws GitBranchNotFoundException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws GitRepoAlreadyExistsException
	 */
	public static function createBranch(string $branch, string $branchFrom = null): string{
		if(!$branchFrom){
			$branchFrom = static::DEFAULT_BRANCH;
		}
		$res = static::runCommand("branch $branch origin/$branchFrom");
		static::setUpstream($branch);
		return $res;
	}
	/**
	 * Runs a `git checkout` call
	 * Accepts a name for the branch
	 * @access  public
	 * @param string $branch branch name
	 * @return  string
	 */
	public static function checkout(string $branch): string{
		return static::runCommand("checkout " . escapeshellarg($branch));
	}
	/**
	 * @param string $message
	 * @param string|null $branch
	 * @param string $files
	 */
	public static function addCommitPush(string $message, string $branch = null, string $files = "."): void{
		static::addAndCommit($message, $files);
		static::push($branch);
	}
	/**
	 * @param string $message
	 * @param string|null $branch
	 * @param string $remote
	 * @return string
	 */
	public static function addAllCommitAndPush(string $message, string $branch = null,
		string $remote = "origin"): string{
		static::logWithGithubUrl(__FUNCTION__);
		if(!$branch){
			$branch = static::getBranchFromGit();
		}
		static::add(".");
		static::commit($message, true);
		/** @noinspection PhpUnhandledExceptionInspection */
		return static::push($branch, $remote);
	}
	/**
	 * @param string $folder
	 * @param string $fileName
	 * @param string $content
	 * @param string $commitMessage
	 * @return array
	 * @throws NoFileChangesException
	 */
	public static function updateOrCreateByAPI(string $folder, string $fileName, string $content,
		string $commitMessage): array{
		static::validateLocalFileDifference($folder, $fileName, $content);
		$oldFile = static::getOldFileArray($folder, $fileName);
		if($oldFile){
			$fileInfo = static::updateFile($folder, $fileName, $content, $commitMessage);
		} else{
			$fileInfo = static::createFile($folder, $fileName, $content, $commitMessage);
		}
		return $fileInfo;
	}
	/**
	 * @param string $folder
	 * @param string $fileName
	 * @param string $content
	 * @throws NoFileChangesException
	 */
	private static function validateLocalFileDifference(string $folder, string $fileName, string $content): void{
		$localContent = static::getLocalFileContents($folder, $fileName);
		if($localContent && $localContent === $content){
			throw new NoFileChangesException("Local $fileName has not changed! ");
		}
	}
	/**
	 * @param string $folderOrFilePath
	 * @param string|null $fileName
	 * @return false|string
	 */
	public static function getLocalFileContents(string $folderOrFilePath, string $fileName = null){
		$absPath = static::getLocalFilePath($folderOrFilePath, $fileName);
		try {
			$contents = file_get_contents($absPath);
			return $contents;
		} catch (Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return null;
		}
	}
	/**
	 * @param string $folderOrFilePath
	 * @param string|null $fileName
	 * @return string
	 */
	protected static function getLocalFilePath(string $folderOrFilePath, string $fileName = null): string{
		$path = static::getLocalFileFolder($folderOrFilePath);
		if($fileName){
			$path .= DIRECTORY_SEPARATOR . $fileName;
		}
		return $path;
	}
	/**
	 * @param string $folderOrFilePath
	 * @return string
	 */
	private static function getLocalFileFolder(string $folderOrFilePath): string{
		$path = static::getAbsPath();
		return FileHelper::absPath($path . DIRECTORY_SEPARATOR . $folderOrFilePath);
	}
	/**
	 * @param string $path
	 * @param string $fileName
	 * @return array|string
	 */
	private static function getOldFileArray(string $path, string $fileName){
		$path .= DIRECTORY_SEPARATOR . $fileName;
		if(isset(static::$oldFiles[$path])){
			return static::$oldFiles[$path];
		}
		$contents = static::contents();
		try {
			$oldFile = $contents->show(static::USERNAME, static::getRepoName(), $path, static::DEFAULT_BRANCH);
			return static::$oldFiles[$path] = $oldFile;
		} catch (Throwable $e) {
			if($e->getMessage() === "Not Found"){
				QMLog::info(__METHOD__.": ".$e->getMessage());
				return static::$oldFiles[$path] = false;
			}
			throw $e;
		}
	}
	/**
	 * @return Contents
	 */
	private static function contents(): Contents{
		$github = static::github();
		/** @var Repo $api */
		$api = $github->api('repo');
		$contents = $api->contents();
		return $contents;
	}
	/**
	 * @param string $folder
	 * @param string $fileName
	 * @param string $content
	 * @param string|null $message
	 * @return array
	 * @throws NoFileChangesException
	 */
	private static function updateFile(string $folder, string $fileName, string $content,
		string $message = null): array{
		static::validateRemoteFileDifference($folder, $fileName, $content);
		$oldFile = static::getOldFileArray($folder, $fileName);
		if(!$message){
			$message = $fileName;
		}
		$fileInfo = static::contents()
			->update(static::USERNAME, static::getRepoName(), $folder . DIRECTORY_SEPARATOR . $fileName, $content, $message,
				$oldFile['sha'], static::DEFAULT_BRANCH, static::COMMITTER);
		QMLog::infoWithoutContext("Updated " . $fileInfo["commit"]["html_url"]);
		FileHelper::writeByDirectoryAndFilename(static::getLocalFileFolder($folder), $fileName, $content);
		return $fileInfo;
	}
	/**
	 * @param string $folder
	 * @param string $fileName
	 * @param string $content
	 * @throws NoFileChangesException
	 */
	private static function validateRemoteFileDifference(string $folder, string $fileName, string $content): void{
		$oldFile = static::getOldFileArray($folder, $fileName);
		$oldContent = QMStr::removeLineBreaks($oldFile['content']);
		$newContent = QMStr::removeLineBreaks(base64_encode($content));
		if($oldContent === $newContent){
			throw new NoFileChangesException("Remote $fileName content has not changed! ");
		}
	}
	/**
	 * @param string $folder
	 * @param string $fileName
	 * @param string $content
	 * @param string|null $message
	 * @return array
	 */
	private static function createFile(string $folder, string $fileName, string $content,
		string $message = null): array{
		if(!$message){
			$message = $fileName;
		}
		try {
			$fileInfo = static::contents()
				->create(static::USERNAME, static::getRepoName(), $folder . DIRECTORY_SEPARATOR . $fileName, $content, $message,
					static::DEFAULT_BRANCH, static::COMMITTER);
		} catch (MissingArgumentException $e) {
			le($e);
		}
		QMLog::infoWithoutContext("Created " . $fileInfo["commit"]["html_url"]);
		FileHelper::writeByDirectoryAndFilename(static::getLocalFileFolder($folder), $fileName, $content);
		return $fileInfo;
	}
	public static function issues(array $params = []): array{
		$u = static::getUsernameFromRequestOrFile();
		$r = QMRequest::getParam('repository', static::getRepoName());
		$issues = static::github()->issues()->all($u, $r, $params);
		return $issues;
	}
	/**
	 * @return string
	 */
	protected static function getUsernameFromRequestOrFile(): string{
		$u = QMRequest::getParam('username', static::USERNAME);
		return $u;
	}
	public static function labels(array $params = []): array{
		$u = static::getUsernameFromRequestOrFile();
		$r = QMRequest::getParam('repository', static::getRepoName());
		$labels = static::repoClient()->labels()->all($u, $r);
		return $labels;
	}
	/**
	 * @return string
	 */
	public static function getOwnerName(): string{
		return static::getUserName();
	}
	public static function cloneOrPullIfNecessary(){
		$repoPath = static::getAbsPath();
		if(FileHelper::folder_exist($repoPath)){
			try {
				static::pull();
			} catch (\Throwable $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
				static::stash();
				static::pull();
			}
		} else{
			static::cloneRepo();
		}
	}
	public static function addStash(){
		static::command("add *"); // https://stackoverflow.com/questions/17404316/the-following-untracked-working-tree-files-would-be-overwritten-by-merge-but-i
		static::stash();
	}
	/** @noinspection PhpUnusedParameterInspection */
	/**
	 * @param string $command
	 * @return string
	 * @throws GitAlreadyUpToDateException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws GitRepoAlreadyExistsException
	 * @throws GitBranchNotFoundException
	 */
	public static function command(string $command): string{
		return static::runCommand($command);
	}
	/**
	 * @param string $relativePath
	 * @return array|string
	 */
	public static function getContentsViaApi(string $relativePath){
		$result =
			static::contents()->show(static::USERNAME, static::getRepoName(), $relativePath, static::DEFAULT_BRANCH);
		return $result;
	}
	/**
	 * @param string $relativeFilePath
	 * @throws GitAlreadyUpToDateException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws GitRepoAlreadyExistsException
	 */
	public static function resetFile(string $relativeFilePath){
		static::runCommand("checkout $relativeFilePath");
	}
	/**
	 * @return object
	 */
	public static function getPackageJson(): object{
		$packageJson = static::getLocalFileContents('', 'package.json');
		return json_decode($packageJson, false);
	}
	/**
	 * @param string $tagNumber
	 * @throws GitAlreadyUpToDateException
	 * @throws GitConflictException
	 * @throws GitLockException
	 * @throws GitNoStashException
	 * @throws GitRepoAlreadyExistsException
	 */
	public static function createTag(string $tagNumber){
		static::runCommand('tag ' . $tagNumber);
		static::push();
	}
	/**
	 * @return array
	 */
	public static function getChangedFiles(): array{
		static::runCommand("reset");
		$string = static::runCommand('diff --name-only');
		$paths = preg_split('/\r\n|\r|\n/', $string);
		$paths = Arr::where($paths, static function($path){
			return !empty($path);
		});
		return $paths;
	}
	/**
	 * @param string $branch
	 * @return string
	 */
	public static function createFeatureBranch(string $branch): string{
		if(stripos($branch, "feature/") !== 0){
			$branch = "feature/$branch";
		}
		try {
			$r = static::createBranch($branch);
			static::push($branch); // Sets up tracking with -u flag
			return $r;
		} catch (\Throwable $e) {
			ConsoleLog::info(__METHOD__.": ".$e->getMessage());
			return static::switchToBranch($branch);
		}
	}
	/**
	 * @param string $branch
	 * @return string
	 */
	public static function switchToBranch(string $branch): string{
		return static::runCommand("checkout $branch");
	}
	/**
	 * @return string
	 */
	public static function configureNameEmailToken(): string{
		$result = static::globalConfig('user.email "m@quantimodo.com"');
		$result = static::globalConfig('user.name "Mike Sinn"');
		$result = static::globalConfig('github.token ' . \App\Utils\Env::get('GITHUB_ACCESS_TOKEN'));
		return $result;
	}
	/**
	 * @param string $str
	 * @return string
	 */
	protected static function globalConfig(string $str): string{
		try {
			return static::runCommand('config --global ' . $str);
		} catch (GitAlreadyUpToDateException | GitConflictException | GitLockException | GitNoStashException | GitRepoAlreadyExistsException $e) {
			le($e);
		}
	}
	/**
	 * @return Statuses
	 */
	public static function languages(): Statuses{
		return static::client()->api('repo')->statuses();
	}
	/**
	 * @param string $name
	 * @param string $head_sha
	 * @param string $details_url
	 * @param string $status queued, in_progress, or completed. Default: queued
	 * @return array
	 */
	public static function createCheck(string $name, string $head_sha, string $details_url, string $status): array{
		$params = get_defined_vars();
		unset($params['sha']);
		$status = $params['status'];
		if(!in_array($status, ['queued', 'in_progress', 'completed'])){
			le("Invalid status: $status");
		}
		$checkRuns = static::checks();
		return $checkRuns->create(static::getUserName(), static::getRepoName(), $params);
	}
	public static function showCheck(string $check_id): array{
		return static::checks()->show(static::getUserName(), static::getRepoName(), $check_id);
	}
	/**
	 * @return CheckRuns
	 */
	public static function checks(): CheckRuns{
		$client = static::client();
		$abstractApi = $client->api('repo');
		return $abstractApi->checkRuns();
	}
	public static function setStatusFailed($exception): array{
		
		return static::createStatus(static::getLongCommitShaHash(), 'failure', 
		                         null, 'default', 'default');
	}
	/**
	 * @param string $sha
	 * @param string $state The state of the status. Can be one of error, failure, pending, or success.
	 * @param string|null $target_url The target URL to associate with this status.
	 * @param string $shortName A string label to differentiate this status from the status of other systems. Default:
	 *     default
	 * @param string $longerSecondaryDescription A short description of the status.
	 * @return array
	 */
	public static function createStatus(string $sha, string $state, ?string $target_url, string $shortName,
		string $longerSecondaryDescription): array{
		try {
			$params = get_defined_vars();
			lei(strlen($shortName) > 255, "Github status context must be shorter than 255 characters but is: $shortName");
			$description = QMStr::truncate($longerSecondaryDescription, 140);
			if(empty($description)){le("No description for " . __FUNCTION__);}
			$context = QMStr::truncate($shortName, 255);
			lei($target_url && stripos($target_url, 'http') !== 0,
				"target_url for github status should not be $target_url");
			$s = QMAPIRepo::statuses();
			$u = static::getUserName();
			$r = static::getRepoName();
			$context = QMStr::removeIfFirstCharacter('/', $context);
			//FirebaseGlobalPermanent::set(static::getFBPath($sha), $params);
			$result = $s->create($u, $r, $sha, [
				'state' => $state,
				'target_url' => $target_url,
				'description' => $description,
				'context' => $context,
			]);
			QMLog::debug("Set Github status $state at " . $result['url'], $result);
			return $result;
		} catch (MissingArgumentException $e) {
			le($e);
		}
	}
	/**
	 * @return Statuses
	 */
	public static function statuses(): Statuses{
		return static::repoClient()->statuses();
	}
	/**
	 * @return string
	 */
	public static function getLongCommitShaHash(): string{
		if($env = Env::get(JenkinsAPI::GIT_COMMIT_SHA_HASH)){return $env;}
		if($env = Env::get('GITHUB_SHA')){return $env;}
		if($env = Env::get('COMMIT_SHA')){return $env;}
		if($sha = static::$commitSha){return $sha;}
		return static::$commitSha = static::getLongCommitShaHashFromGit();
	}
	/**
	 * @return string
	 */
	public static function getLongCommitShaHashFromGit(): string{
		$repo = self::gitPHP();
		$commitId = $repo->getLastCommitId();
		return $commitId->toString();
	}
	/**
	 * @param string|null $branch
	 * @return string
	 */
	public static function getCloneBranchCommand(string $branch = null): string{
		if(!$branch){
			$branch = static::DEFAULT_BRANCH;
		}
		$url = static::getUrlWithToken();
		return "git clone -b $branch $url";
	}
	/**
	 * @return string
	 */
	public static function getUrlWithToken(): string{
		$url = "https://" . \App\Utils\Env::get('GITHUB_ACCESS_TOKEN') . "@github.com/" . static::getOwnerRepo() . ".git";
		return $url;
	}
	/**
	 * @param string $sha
	 * @param string $destination
	 * @return string[]
	 */
	public static function getCloneOrPullShaCommands(string $sha, string $destination): array{
		$url = static::getUrlWithToken();
		$commands = [
			"git clone --depth 1 $url $destination || true",
			"cd $destination && git remote add origin $url || true",
			"cd $destination && git fetch origin $sha --depth=10",
			"cd $destination && git reset --hard FETCH_HEAD",
		];
		return $commands;
	}
	public static function getResetBranchCommands(string $branch = null): array{
		if(!$branch){
			$branch = static::DEFAULT_BRANCH;
		}
		$commands = [
			'git stash',
			'git reset --hard',
			'git fetch',
			"git checkout $branch",
			'git pull --allow-unrelated-histories',  // fixes fatal: refusing to merge unrelated
		];
		return $commands;
	}
	/**
	 * @param string $markdown
	 * @param string|null $sha
	 * @return array
	 */
	public static function createComment(string $markdown, string $sha = null): array{
		QMStr::assertStringShorterThan(65536, $markdown, "github-comment");
		if(!$sha){
			$sha = static::getLongCommitShaHash();
		}
		try {
			return static::comments()
				->create(static::getUserName(), static::getRepoName(), $sha, ['body' => $markdown]);
		} catch (MissingArgumentException $e) {
			le($e);
		} catch (Throwable $e) {
            ConsoleLog::info(" Could not comment on repo " .static::getUserName()."/".
            static::getRepoName() . "
because: " . $e->getMessage());
			return [];
        }
	}
	/**
	 * @return Comments
	 */
	public static function comments(): Comments{
		return static::client()->api('repo')->comments();
	}
	public static function getCommitButton(string $sha = null): QMButton{
		if(!$sha){
			$sha = static::getLongCommitShaHash();
		}
		$msg = static::getCommitMessage($sha);
		$url = static::githubCommitUrl($sha);
		$b = new GithubCommitButton($msg, $url);
		return $b;
	}
	public static function getCommitMessage(string $sha = null): string{
		if(!$sha){
			$sha = static::getLongCommitShaHash();
		}
		if($msg = static::$message){
			return $msg;
		}
		$msg = static::command("log -n 1 --pretty=format:%s $sha");
		if(!$msg){
			$msg = "Could not get commit message";
		}
		return static::$message = $msg;
	}
	public static function githubCommitUrl(string $sha = null): string{
		$owner = static::getOwnerRepo();
		return "https://github.com/$owner/commit/$sha";
	}
	public static function getBranchButton(string $sha = null): QMButton{
		$name = static::getBranchFromMemoryOrGit();
		$b = new GithubBranchButton($name, static::getBranchUrl());
		return $b;
	}
	public static function getBranchFromMemoryOrGit(): string{
		if($b = Env::get('GITHUB_REF_NAME')){return $b;}
		if($b = Env::get('BRANCH_NAME')){return $b;}
		if($b = Env::get('GIT_BRANCH')){
			return str_replace("/origin", "", $b);
		}
		$b = static::getBranchFromMemory();
		return static::getBranchFromGit();
	}
	public static function getBranchFromMemory():?string{
		if(!empty(self::$branchByRepo[static::class])){
			return self::$branchByRepo[static::class];
		}
		return null;
	}
	public static function getBranchUrl(): string{
		return static::getGithubUrl() . "/tree/" . static::getBranchFromMemoryOrGit();
	}
	/**
	 * @param string $branch_name
	 * @return string
	 */
	public static function deleteLocalBranch(string $branch_name): string{
		ConsoleLog::info(__FUNCTION__);
		try {
			return static::command("branch -D $branch_name");
		} catch (GitBranchNotFoundException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return $e->getMessage();
		}
	}
	/**
	 * @param string $branch_name
	 * @return string
	 */
	public static function deleteRemoteBranch(string $branch_name): string{
		ConsoleLog::info(__FUNCTION__);
		try {
			return static::command("push origin --delete $branch_name");
		} catch (GitBranchNotFoundException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return $e->getMessage();
		}
	}
	public static function deleteLocalFeatureBranches(){
		static::deleteLocalBranchesLike("feature");
	}
	/**
	 * @param string $pattern
	 */
	public static function deleteLocalBranchesLike(string $pattern){
		$branches = static::list_local_branches();
		foreach($branches as $branch_name){
			if(!str_contains($branch_name, $pattern)){
				continue;
			}
			try {
				static::command("branch -D $branch_name");
			} catch (\Throwable $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
			}
		}
	}
	public static function cloneSubModules(){
		QMLog::logStartOfProcess(__FUNCTION__);
		//IonicRepo::clonePullAndOrUpdateRepo();  Included as submodule of builder repo
		$classes = FileHelper::getClassesInFolder("app/Repos");
		/** @var GitRepo $class */
		foreach($classes as $class){
			if($class === GitRepo::class){continue;}
			if($class === QMAPIRepo::class){continue;}
			if($class === PHPUnitTestRepo::class){continue;}
			if($class === WPRepo::class){continue;}
			$class::cloneIfNecessary();
		}
		QMLog::logEndOfProcess(__FUNCTION__);
	}
	public static function pullAndBuild(){
		static::clonePullAndOrUpdateRepo();
		static::build();
	}
	public static function build(){
		$repo = static::getAbsPath();
		if(empty($repo)){
			le('empty($repo)');
		}
		ThisComputer::exec("cd $repo && npm install || true");
		ThisComputer::exec("cd $repo && gulp || true");
		ThisComputer::exec("cd $repo && composer install || true");
	}
	public static function deleteAndReCloneIfNecessary(){
		static::deleteIfGitFolderNotPresent();
		static::cloneIfNecessary();
	}
	/**
	 */
	public static function deleteIfGitFolderNotPresent(): void{
		$repoPath = static::getAbsPath();
		//$absPath = FileHelper::folder_exist($repoPath);
		$gitFolder = FileHelper::folder_exist($repoPath . "/.git");
		if(!$gitFolder){
			static::deleteRepo("git folder does not exist!");
		}
	}
	public static function deleteRepo(string $reason){
		$repoPath = static::getAbsPath();
		QMLog::error("Deleting $repoPath because: $reason!");
		FileHelper::deleteDir($repoPath);
	}
	public static function getSecondsSinceLastModified(string $relative): ?int{
		return FileHelper::getSecondsSinceLastModified(static::getAbsolutePath($relative));
	}
	public static function logGithubUrlForBranchComparison(string $branch, string $base = null): string{
		if(!$base){
			$base = static::DEFAULT_BRANCH;
		}
		$url = static::getGithubUrl() . "/compare/$base...$branch";
		ConsoleLog::info("
            Compare changes at:
                $url
        ");
		return $url;
	}
	/**
	 * Gets the rate limit resource objects.
	 * @return RateLimitResource[]
	 */
	public static function getRateLimits(): array{
		$client = static::rateLimitClient();
		return $client->getResources();
	}
	public static function rateLimitClient(): RateLimit{
		$client = static::client()->api('rate_limit');
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $client;
	}
	public static function sleepIfNecessary(){
		$core = static::getCoreRateLimits();
		if(!$core->getRemaining()){
			$resetTime = $core->getReset();
			while(time() < $resetTime){
				QMLog::info("Sleeping " . TimeHelper::convertSecondsToHumanString($core->getReset() - time()) .
					" until more Github requests are available...");
				if(EnvOverride::isLocal()){
					return;
				}
				sleep(60);
			}
		} else{
			static::logRemainingRequests($core);
		}
	}
	/**
	 * Gets the rate limit resource objects.
	 * @return RateLimitResource
	 */
	public static function getCoreRateLimits(): RateLimitResource{
		$c = static::rateLimitClient()->getResources();
		return $c["core"];
	}
	/**
	 * @param RateLimitResource $r
	 * @return string
	 */
	private static function logRemainingRequests(RateLimitResource $r): string{
		return "=== {$r->getRemaining()} core requests remaining! More available in " .
			TimeHelper::convertSecondsToHumanString($r->getReset() - time()) . ". ===";
	}
	public static function getComments(string $sha = null): array{
		if(!$sha){
			$sha = static::getLongCommitShaHash();
		}
		return static::comments()->show(static::USERNAME, static::getRepoName(), $sha);
	}
	public static function exceptionIfHasFailedStatus(){
		$job = JenkinsJob::getCurrentJobName();
		if(stripos($job, 'Production-') === false && stripos($job, "StagingUnit") === false){
			return;
		}
		if($failed = static::getFailedStatuses()){
			$queued = JenkinsQueue::getQueuedItemsByJobName();
			$count = count($queued);
			if(!$count){
				ConsoleLog::info("Not cancelling despite other tests failing because " .
					"nothing else is queued for job: $job anyway...");
				return;
			}
			ConsoleLog::info("Cancelling test because we already failed this SHA and there" . " are $count queued $job jobs.
                 FAILED STATUS CHECKS: " . QMLog::print_r(collect($failed)->map(function($status){
					$status = (object)$status;
					return [
						$status->state,
						$status->context,
						$status->description,
						$status->target_url,
					];
				}), true));
			exit(0);
		}
	}
	public static function getFailedStatuses(string $sha = null): array{
		if(!$sha){
			$sha = static::getLongCommitShaHash();
		}
		try {
			$statuses = static::getStatuses($sha);
		} catch (Throwable $e) { // Avoid abuse errors
			QMLog::error(__METHOD__.": ".$e->getMessage());
			return [];
		}
		$failed = collect($statuses)->filter(function($status){
			$state = $status["state"];
			$errored = $state === static::STATE_error;
			$failed = $state === static::STATE_failure;
			$res = $errored || $failed;
			return $res;
		})->all();
		$pending =
			collect($statuses)->filter(function($status){ return $status["state"] === static::STATE_pending; })->all();
		$success =
			collect($statuses)->filter(function($status){ return $status["state"] === static::STATE_success; })->all();
		ConsoleLog::info(count($statuses) . " TOTAL STATUSES for SHA $sha");
		ConsoleLog::info(count($failed) . " FAILED OR ERRORED statuses for SHA $sha");
		ConsoleLog::info(count($success) . " SUCCESS statuses for SHA $sha");
		foreach($failed as $status){
			$status = (object)$status;
			ConsoleLog::info("$status->state $status->context $status->description $status->target_url");
		}
		return $failed;
	}
	public static function setStatusSuccessful(string $shortName, string $longerSecondaryDescription): array
	{
		return static::createStatus(static::getLongCommitShaHash(),
		                            "success", ThisComputer::getBuildUrl(), $shortName,
		                            $longerSecondaryDescription);
	}
	public static function setStatusPending(string $shortName, string $longerSecondaryDescription): array
	{
		return static::createStatus(static::getLongCommitShaHash(),
		                            "pending", ThisComputer::getBuildUrl(), $shortName,
		                            $longerSecondaryDescription);
	}
	public static function getStatuses(string $sha = null): array{
		if(!$sha){
			$sha = static::getLongCommitShaHash();
		}
		return static::statuses()->show(static::USERNAME, static::getRepoName(), $sha);
	}
	public static function statusesForContext(string $context): Collection {
		$sha = static::getLongCommitShaHash();
		$statuses = static::getStatuses($sha);
		$hasStatus = collect($statuses)->filter(function($status) use ($context){
			return $status["context"] === $context;
		});
		return $hasStatus;
	}
	public static function downloadFromS3(){
		S3Public::downloadFolder(static::S3_PATH, static::getAbsPath());
	}
	/**
	 * @param string $relativeFilePath
	 * @param $data
	 * @return string
	 */
	public static function writeJsonFile(string $relativeFilePath, $data): string{
		$path = str_replace('//', DIRECTORY_SEPARATOR, static::getAbsPath() . DIRECTORY_SEPARATOR . $relativeFilePath);
		return FileHelper::writePrettyJson($path, $data, static::getBlackListedStrings());
	}
	public static function getAgeOfFileInSeconds(string $relativeFilePath): ?int{
		$path = str_replace('//', DIRECTORY_SEPARATOR, static::getAbsPath() .DIRECTORY_SEPARATOR . $relativeFilePath);
		return FileHelper::getAgeOfFileInSeconds($path);
	}
	public static function cloneImportantSubmodules(){
		QMLog::logStartOfProcess(__FUNCTION__);
		//StaticDataRepo::cloneIfNecessary();
		//XhguiRepo::cloneIfNecessary();
		//BuilderRepo::cloneIfNecessary();
		IonicRepo::cloneIfNecessary();
		//ImagesRepo::cloneIfNecessary();
		QMLog::logEndOfProcess(__FUNCTION__);
	}
	public static function updateImportantSubmodules(){
		$last = QMLog::getLastProcessStartTime(__FUNCTION__);
		$human = TimeHelper::timeSinceHumanString($last);
		QMLog::info(__FUNCTION__ . " last run $human");
		if($last > time() - 24 * 3600){
			self::cloneImportantSubmodules();
			return;
		}
		QMLog::logStartOfProcess(__FUNCTION__);
		//StaticDataRepo::clonePullAndOrUpdateRepo();
		//XhguiRepo::clonePullAndOrUpdateRepo();
		//BuilderRepo::clonePullAndOrUpdateRepo();
		IonicRepo::clonePullAndOrUpdateRepo();
		//HomesteadRepo::clonePullAndOrUpdateRepo();
		//WPRepo::clonePullAndOrUpdateRepo();
		//QMWPPluginRepo::clonePullAndOrUpdateRepo();
		//WPRepo::build();
		//DocsRepo::clonePullAndOrUpdateRepo();
		//ApplicationSettingsRepo::clonePullAndOrUpdateRepo();
		//MoneyModoRepo::clonePullAndOrUpdateRepo();
		//ImagesRepo::cloneIfNecessary();
		//TestResultsRepo::clonePullAndOrUpdateRepo();
		QMLog::logEndOfProcess(__FUNCTION__);
	}
	public static function updateAllSubmodules(): void{
		QMLog::logStartOfProcess(__FUNCTION__);
		$all = static::all();
		foreach($all as $repo){
			$repo->clonePullAndOrUpdateRepo();
		}
		QMLog::logEndOfProcess(__FUNCTION__);
	}
	/**
	 * @return static[]
	 */
	private static function all(): array{
		$models = PhpClassFile::instantiateModelsInFolder(AppReposFolder::getAbsolutePath(), true, [GitRepo::class]);
		return $models;
	}
	public static function cleanResetAndUpdateSubModules(){
		ConsoleLog::info("Cleaning submodules..");
		static::command("submodule foreach --recursive git clean -xfd");
		ConsoleLog::info("Resetting submodules..");
		static::command("submodule foreach --recursive git reset --hard");
		ConsoleLog::info("Updating submodules..");
		static::command("submodule update --init --recursive");
	}
	public static function checkoutCommit(string $sha){
		static::command("reset --hard $sha");
	}
	public static function mergeDevelopIntoLocalFeatureBranches(){
		$branches = static::list_local_branches();
		foreach($branches as $branch){
			if(strpos($branch, 'origin/feature/') === 0){
				static::execute("-c core.quotepath=false -c log.showSignature=false checkout $branch");
				static::execute("-c core.quotepath=false -c log.showSignature=false fetch origin --recurse-submodules=no --progress --prune");
				static::execute("-c core.quotepath=false -c log.showSignature=false merge $branch --no-stat -v");
				static::execute("git -c core.quotepath=false -c log.showSignature=false fetch origin develop:develop --recurse-submodules=no --progress --prune");
				static::execute("");
				static::execute("");
				static::execute("");
				static::execute("");
			}
		}
	}
	public static function execute(string $cmd, bool $obfuscate = true): string {
		return ThisComputer::exec("cd " . static::getAbsolutePath() . " && $cmd", $obfuscate);
	}
	public static function rebase(string $branchToPull = "origin/develop"){
		static::command("rebase $branchToPull");
	}
	public static function updateFeatureBranches(): void{
		static::mergeDevelopIntoFeatureBranches();
	}
	public static function mergeDevelopToMaster(){
		static::checkout("master");
		static::fetch();
		static::merge("develop");
		static::push("master");
	}
	public static function mergeDevelopIntoFeatureBranches(){
		static::checkoutAndPullDevelop();
		$branches = static::list_remote_branches();
		foreach($branches as $branch){
			if(str_starts_with($branch, 'origin/feature/')){
				static::checkout($branch);
				static::fetch();
				static::merge("develop");
				static::push($branch);
			}
		}
	}
	public static function checkoutAndPullDevelop(): void{
		static::checkout("develop");
		static::pull("develop");
	}
	/**
	 * Runs a `git branch` call
	 * @param bool $keep_asterisk
	 * @param bool $outputToConsole
	 * @return  array
	 */
	public static function list_remote_branches(bool $keep_asterisk = false, bool $outputToConsole = false): array{
		$output = static::runCommand("branch -r", $outputToConsole);
		return static::branchOutputToArray($output, $keep_asterisk);
	}
	/**
	 * Runs a `git merge` call
	 * Accepts a name for the branch to be merged
	 * @access  public
	 * @param string $branch
	 * @return  string
	 */
	public static function merge(string $branch): string{
		return static::runCommand("merge " . escapeshellarg($branch));
	}
	public static function popStash(){
		static::command("stash pop");
	}
	public static function addFilesInFolder(string $relativeToRepo): string{
		$abs = static::getAbsolutePath($relativeToRepo);
		return ThisComputer::exec("cd $abs && git add *");
	}
	public static function getRepoResponseObject(string $username = null,
		string $repoName = null): \App\DataSources\Connectors\Responses\Github\Repo{
		$key = "$username/$repoName";
		//       Messes up tests $cached = LocalCache::get($key, __FUNCTION__);
		//        if($cached){return $cached;}
		ConsoleLog::info("Getting repo data for $username/$repoName");
		try {
			$data = self::getRepoInfoFromGithubAPI($username, $repoName);
			$repo = new \App\DataSources\Connectors\Responses\Github\Repo($data);
			LocalFileCache::set($key, $repo);
			return $repo;
		} catch (RuntimeException $e) {
			if(stripos($e->getMessage(), "Not Found") !== false){
				throw new NotFoundException($e->getMessage(), [], $e);
			}
			le($e);
		}
	}
	/**
	 * @param string $q
	 * @param string $sort
	 * @param string $order
	 * @return \App\DataSources\Connectors\Responses\Github\Repo[]
	 */
	public static function search(string $q, string $sort = 'stars', string $order = 'desc'): array{
		$key = "$q-$sort-$order-" . __METHOD__;
		$cached = LocalFileCache::get($key);
		if($cached){
			return $cached;
		}
		$github = static::github();
		/** @var Search $api */
		$api = $github->api('search');
		$data = $api->repositories($q, $sort, $order);
		$repos = [];
		foreach($data['items'] as $one){
			$repos[] = new \App\DataSources\Connectors\Responses\Github\Repo($one);
		}
		LocalFileCache::set($key, $repos);
		return $repos;
	}
	/**
	 * @param string $sourceFilePath
	 * @param string $destinationFilePath
	 * @return bool
	 */
	public static function copy(string $sourceFilePath, string $destinationFilePath): bool{
		$sourceFilePath = static::getAbsolutePath($sourceFilePath);
		$destinationFilePath = static::getAbsolutePath($destinationFilePath);
		return FileHelper::copy($sourceFilePath, $destinationFilePath);
	}
	public static function listFilesInFolder(string $folder = null, string $filenameLike = null): array{
		$folder = static::getAbsolutePath($folder);
		return FileFinder::listFilesRecursively($folder, $filenameLike);
	}
	public static function packageJson(): PackageJson{
		return new PackageJson(static::getLocalFilePath('package.json'));
	}
	public static function composerJson(): ComposerJson{
		return new ComposerJson(static::getLocalFilePath('composer.json'));
	}
	public static function deleteFileOrFolder(string $relativeFilePath, string $reason = null){
		$path = static::getAbsolutePath($relativeFilePath);
		FileHelper::deleteFileOrFolder($path, $reason);
	}
	/**
	 * @param string $relativeFilePath
	 * @param bool $assoc
	 * @return mixed
	 * @throws QMFileNotFoundException
	 */
	public static function readJsonFile(string $relativeFilePath, bool $assoc){
		$file = static::getContents($relativeFilePath);
		return json_decode($file, $assoc);
	}
	/**
	 * @param string $relativeFilePath
	 * @return string
	 * @throws QMFileNotFoundException
	 */
	public static function getContents(string $relativeFilePath): string{
		$abs = static::getAbsolutePath($relativeFilePath);
		$abs = str_replace('//', DIRECTORY_SEPARATOR, $abs);
		return FileHelper::getContents($abs);
	}
	/**
	 * @param string $path
	 * @param string|View $html
	 */
	public static function writeHtml(string $path, $html){
		if(!is_string($html)){
			try {
				$html = $html->render();
			} catch (Throwable $e) {
				le($e);
			}
		}
		if(!str_contains($path, ".html")){
			$path .= ".html";
		}
		static::writeToFile($path, $html);
	}
	/**
	 * @param string $filepath
	 * @param mixed $content
	 * @return string
	 */
	public static function writeToFile(string $filepath, $content): string{
		if(!is_string($content)){
			$content = json_encode($content);
		}
		$filepath = static::getAbsolutePath($filepath);
		return FileHelper::writeByFilePath($filepath, $content);
	}
	/**
	 * @return string
	 */
	public static function checkRateLimits(): string{
		$r = static::getCoreRateLimits();
		$str = static::logRemainingRequests($r);
		QMLog::info($str);
		QMLog::print($r, "Github Rate Limits");
		return $str;
	}
	public static function getNumberOfRequestsRemaining(): int{
		return static::getCoreRateLimits()->getRemaining();
	}
	public static function org(): \App\DataSources\Connectors\Responses\Github\Repo{
		$data = static::repoClient()->show(static::USERNAME, static::getRepoName());
		return new \App\DataSources\Connectors\Responses\Github\Repo($data);
	}
	public static function branchIsDevelop(): bool{
		return static::branchIsLike("develop");
	}
	public static function branchIsLike(string $branch): bool{
		return stripos(static::getBranchFromMemoryOrGit(), $branch) !== false;
	}
	public static function owner(): Owner{
		return static::repo()->getOwner();
	}
	public static function str_replace(string $search, string $replace, string $subfolder = null){
		FileHelper::replaceStringInAllFilesInFolder(static::getAbsolutePath($subfolder), $search, $replace);
	}

    /**
     * @param string|null $path
     * @return \CzProject\GitPhp\GitRepository
     */
    public static function phpGitRepo(string $path = null){
        if($path === null){
            $path = static::getAbsolutePath();
        }
        $git = new \CzProject\GitPhp\Git;
        // create repo object
        $repo = $git->open($path);
        return $repo;
    }
    private static function getGitBinPath(): string{
        if(AppMode::isWindows()){
            return "C:\\Program Files\\Git\\bin\\git.exe";
        }
        return "/usr/bin/git";
    }
    public static function getOriginRemoteUrl(): string{
		return static::getLastLine(static::exec('git config --get remote.origin.url'));
	}
	/**
	 * @param string|null $url
	 * @param string|null $title
	 */
	public function logLink(string $url = null, string $title = null){
		if(!$url){$url = $this->getOriginRemoteUrl();}
		if(!$title){$title = $this->getTitleAttribute();}
		QMLog::logLink($url, $title);
	}
	public static function getGithubBranchUrl(): string{
		$owner = static::getOwnerRepo();
		$branch = static::getBranchFromMemoryOrGit();
		return "https://github.com/$owner/tree/$branch";
	}
	private static function getLastLine(array $output): string{
		return array_pop($output);
	}
	private static function exec(string $command, string $customErrorMessage = null): array{
		$out = ThisComputer::exec("cd ".static::getAbsolutePath()." && $command");
		return QMStr::explodeNewLines($out);
	}
	public static function getLastCommitHash(): string{
		return static::getLastLine(static::exec('git log -1 --format="%H"'));
	}
	public static function getLastAuthor(): string{
		return static::getLastLine(static::exec('git log -1 --format="%an"'));
	}
	public static function getLastAuthoredDate(): \DateTime{
		return new \DateTime(static::getLastLine(static::exec('git log -1 --format="%ai"')));
	}
	public static function getLastTag(callable $filter = null): string{
		$tags = static::exec('git tag -l --sort=v:refname');
		if(null !== $filter){
			$tags = array_filter($tags, $filter);
		}
		return static::getLastLine($tags);
	}
	/**
	 * @param string $str
	 * @param string $type
	 * @throws InvalidStringException
	 */
	protected static function validateString(string $str, string $type){
		QMStr::assertDoesNotContain($str, static::getBlackListedStrings(), $type);
	}
	public function getFontAwesome(): string{
		return FontAwesome::GITHUB;
	}
	/**
	 * @return string
	 */
	public function __toString(){ return $this->getSlugWithNames(); }
	public function getSlugWithNames(): string{
		return QMStr::slugify(static::getOwnerRepo());
	}
	public function getNameAttribute(): string{ return $this->getSlugWithNames(); }
	protected static function relativeToRepo(string $abs): string{
		$rel = QMStr::after(static::getOwnerRepo() . "/", $abs);
		if(!$rel){
			le("No rel from $abs");
		}
		return $rel;
	}
	protected static function pathRelativeToQM(string $relativeToRepo = null): string{
		if(!$relativeToRepo){
			return static::getRelativePath();
		}
		return static::getRelativePath() . "/$relativeToRepo";
	}
	public static function postUpdate(){
		if(AppMode::isTestingOrStaging()){
			ConsoleLog::info(__METHOD__ . "Skipping npm install & composer install because we're testing.");
			return;
		}
		if(static::fileExists('composer.json')){
			try {
				static::composerInstall();
			} catch (\Throwable $e){
				QMLog::error(static::getOwnerRepo().": Could not composer install because: ". $e->getMessage());
			}
		}
		try {
			if(static::fileExists('package.json')){ // Need bash profile so nvm is available
				static::exec('source ~/.bash_profile && npm install');
			}
		} catch (\Throwable $e) {
		}
	}
	public static function info(string $message){
		ConsoleLog::info(static::getOwnerRepo() . ": $message");
	}
	public static function composerInstall(){
		static::exec("printenv && composer install");
	}
	/**
	 * @return string
	 */
	public static function getShortCommitSha(): string{
		return QMStr::getFirstXChars(static::getLongCommitShaHash(), 7);
	}
	public function getTitleAttribute(): string{
		return static::getOwnerName();
	}
	/**
	 * @return void
	 * @notimplemented
	 * @deprecated Doesn't work yet
	 */
	public static function deleteIssues(){
		le("Not implemented");
		$issues = static::issues();
		foreach($issues as $issue){
			static::github()->issues()->delete($issue->getOwner(), $issue->getRepo(), $issue->getNumber());
		}
	}
}
