<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DataSources\Connectors;
use App\DataSources\Connectors\Responses\Github\Repo;
use App\DataSources\HasUserProfilePage;
use App\DataSources\OAuth2Connector;
use App\DataSources\TooEarlyException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\GithubRepository;
use App\Slim\Controller\Connector\ConnectorException;
use App\Slim\Model\Measurement\AdditionalMetaData;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\Units\EventUnit;
use App\Utils\AppMode;
use App\VariableCategories\ActivitiesVariableCategory;
use App\VariableCategories\GoalsVariableCategory;
use App\Variables\QMUserVariable;
use Github\Api\Repository\Commits;
use Github\Client;
use Github\HttpClient\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Token\StdOAuth2Token;
/**
 * Class GithubConnector
 * @package App\DataSources\Connectors
 */
class GithubConnector extends OAuth2Connector {
	use HasUserProfilePage;
	protected const AFFILIATE                      = false;
	protected const BACKGROUND_COLOR               = '#e4405f';
	protected const CLIENT_REQUIRES_SECRET         = true; // Can't use these yet because we need the response headers
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = ActivitiesVariableCategory::NAME;
	protected const DEVELOPER_CONSOLE              = "https://github.com/organizations/QuantiModo/settings/applications/81979";
	protected const DEVELOPER_PASSWORD             = null;
	protected const DEVELOPER_USERNAME             = "mikepsinn";
	public const    DISPLAY_NAME                   = 'GitHub';
	protected const ENABLED                        = 1;
	protected const GET_IT_URL                     = 'https://github.com/';
	public const    ID                             = 4;
	public const    IMAGE                          = 'https://i.imgur.com/eUiQNlk.png';
	protected const LOGO_COLOR                     = '#2d2d2d';
	protected const LONG_DESCRIPTION               = 'GitHub is the best place to share code with friends, co-workers, classmates, and complete strangers. Over four million people use GitHub to build amazing things together.';
	const           MAX_PER_PAGE                   = 30;  // I think this is the max GitHub will allow
	public const    NAME                           = 'github';
	const           PATH_REPOS                     = "/repos";
	const           PATH_USER                      = "/user";
	const           PATH_USER_REPOS                = self::PATH_USER . self::PATH_REPOS;
	/**
	 * Delete access to repositories.
	 */
	const SCOPE_DELETE_REPO = 'delete_repo';
	/**
	 * Write access to gists.
	 */
	const SCOPE_GIST = 'gist';
	/**
	 * Grants read, write, ping, and delete access to hooks in public or private repositories.
	 */
	const SCOPE_HOOKS_ADMIN = 'admin:repo_hook';
	/**
	 * Grants read and ping access to hooks in public or private repositories.
	 */
	const SCOPE_HOOKS_READ = 'read:repo_hook';
	/**
	 * Grants read, write, and ping access to hooks in public or private repositories.
	 */
	const SCOPE_HOOKS_WRITE = 'write:repo_hook';
	/**
	 * Read access to a user’s notifications. repo is accepted too.
	 */
	const SCOPE_NOTIFICATIONS = 'notifications';
	/**
	 * Defined scopes, see http://developer.github.com/v3/oauth/ for definitions.
	 */
	/**
	 * Fully manage organization, teams, and memberships.
	 */
	const SCOPE_ORG_ADMIN = 'admin:org';
	/**
	 * Read-only access to organization, teams, and membership.
	 */
	const SCOPE_ORG_READ = 'read:org';
	/**
	 * Publicize and unpublicize organization membership.
	 */
	const SCOPE_ORG_WRITE = 'write:org';
	/**
	 * Fully manage public keys.
	 */
	const SCOPE_PUBLIC_KEY_ADMIN = 'admin:public_key';
	/**
	 * List and view details for public keys.
	 */
	const SCOPE_PUBLIC_KEY_READ = 'read:public_key';
	/**
	 * Create, list, and view details for public keys.
	 */
	const SCOPE_PUBLIC_KEY_WRITE = 'write:public_key';
	/**
	 * Read/write access to public repos and organizations.
	 */
	const SCOPE_PUBLIC_REPO = 'public_repo';
	/**
	 * Public read-only access (includes public user profile info, public repo info, and gists)
	 */
	const SCOPE_READONLY = '';
	/**
	 * Read/write access to public and private repos and organizations.
	 * Includes SCOPE_REPO_STATUS.
	 */
	const SCOPE_REPO = 'repo';
	/**
	 * Grants access to deployment statuses for public and private repositories.
	 * This scope is only necessary to grant other users or services access to deployment statuses,
	 * without granting access to the code.
	 */
	const SCOPE_REPO_DEPLOYMENT = 'repo_deployment';
	/**
	 * Read/write access to public and private repository commit statuses. This scope is only necessary to grant other
	 * users or services access to private repository commit statuses without granting access to the code. The repo and
	 * public_repo scopes already include access to commit status for private and public repositories, respectively.
	 */
	const SCOPE_REPO_STATUS = 'repo:status';
	/**
	 * Read/write access to profile info only.
	 * Includes SCOPE_USER_EMAIL and SCOPE_USER_FOLLOW.
	 */
	const SCOPE_USER = 'user';
	/**
	 * Read access to a user’s email addresses.
	 */
	const SCOPE_USER_EMAIL = 'user:email';
	/**
	 * Access to follow or unfollow other users.
	 */
	const           SCOPE_USER_FOLLOW = 'user:follow';
	protected const SHORT_DESCRIPTION = 'Tracks code commits.';
	public static $BASE_API_URL = "https://api.github.com";
	public static $OAUTH_SERVICE_NAME = 'GitHub';
	public static array $SCOPES = [
		'user',
		'repo',
	];
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public bool $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $fontAwesome = FontAwesome::GITHUB;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $providesUserProfileForLogin = true;
	public array $repositoryImages = [];
	public $shortDescription = self::SHORT_DESCRIPTION;
	protected array $repositories = [];
	protected $useFileResponsesInTesting = false;
	private GithubRepository $currentRepo;
	private string $currentRepositoryName;
	private $githubRepos;
	private $contributions;
	/**
	 * @return string
	 */
	public function getAbsoluteFromAt(): string{
		return db_date(time() - 6 * 365 * 86400);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAccessTokenEndpoint(): \OAuth\Common\Http\Uri\UriInterface|Uri{
		return new Uri('https://github.com/login/oauth/access_token');
	}
	/**
	 * {@inheritdoc}
	 */
	public function getAuthorizationEndpoint(): \OAuth\Common\Http\Uri\UriInterface|Uri{
		return new Uri('https://github.com/login/oauth/authorize');
	}
	/**
	 * @return void
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 */
	public function importData(): void{
		$user = $this->getConnectorUserProfile();
		//$commits = $this->repoCommits([], "qm-api");
		$this->importFromEvents();
		$this->importFromRepos();
		$this->saveMeasurements();
	}

    /**
     * @return array
     */
    public function getRepositoryImages(): array{
        return $this->repositoryImages;
    }

    /**
     * @param array $repositoryImages
     */
    public function setRepositoryImages(array $repositoryImages): void
    {
        $this->repositoryImages = $repositoryImages;
    }
	/**
	 * @return void
	 * @throws TokenNotFoundException
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	public function importFromRepos(): void{
		$repos = $this->getRepositories();
		$total = count($repos);
		if($total === 0){
			$this->logInfo("User has no repositories!");
			return;
		}
		$i = 0;
		foreach($repos as $repo){
			if(!$this->inAllowedRepos($repo->full_name)){
				$this->logInfo("Skipping $repo->full_name because it's not in allowed repos");
				continue;
			}
			$this->currentRepo = $repo;
			$uv = $this->getCommitsUserVariable();
			$latestMeasurementAt = $uv->getLatestTaggedMeasurementAt();
			$i++;
			$progress = "($i of $total repos imported)";
			if(strtotime($repo->pushed_at) < strtotime($latestMeasurementAt)){
				$this->logInfoWithoutContext("Skipping $repo->full_name because last push was $repo->pushed_at $progress");
				continue;
			}
			$this->logInfoWithoutContext("Getting commits for $repo->full_name $progress");
			$this->getAllCommitsForSingleRepository();
			if($this->weShouldBreak()){
				break;
			}
		}
	}
	public function importFromEvents(): void{
		$events = $this->getEvents();
		$total = count($events);
		if($total === 0){
			$this->logInfo("User has no events!");
			return;
		}
		foreach($events as $event){
			$repoFullName = $event["repo"]["name"];
			if(!isset($event["payload"]["commits"])){
				if(isset($event["payload"]["comment"])){
					$this->logInfo("Skipping $repoFullName event because it's a comment".QMLog::print_r($event));
				} else {
					$this->logInfo("No commits in $repoFullName event payload: ".QMLog::print_r($event));
				}
				continue;
			}
			$this->currentRepo = $this->getRepo($repoFullName);
			$v = $this->getCommitsUserVariable();
			$note = new AdditionalMetaData();
			$note->image = $this->currentRepo->getImage();
			$note->url = "https://github.com/$repoFullName/commit/".$event["payload"]["commits"][0]["sha"];
			$note->message = "";
			$value = count($event["payload"]["commits"]);
			foreach($event["payload"]["commits"] as $commit){
				$note->message .= $commit["message"]."\n";
			}
			$this->addMeasurement($v->getVariableName(), $event["created_at"], $value, EventUnit::NAME, $v->getVariableCategoryName(), [], null,
			                      $note);
		}
	}
	/**
	 * @return array
	 */
	public function fetchRepos(): array{
		$this->logInfo(__FUNCTION__."...");
		$client = $this->githubClient();
		$userApi = $client->api('me');
		$paginator = new \Github\ResultPager($client);
		$parameters = [
			//'username' => $this->getConnectorUserName(),
			'visibility' => 'all',
			//'visibility' => 'private',
			'sort' => 'updated_at',
			'direction' => 'desc'
		];
		$newRepos = $paginator->fetchAll($userApi, 'repositories', $parameters);
		return $newRepos;
	}
	/**
	 * @param array $gRepo
	 * @param array $cols
	 * @return void
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	public function saveRepo(array $gRepo): GithubRepository {
		$this->githubRepos[$gRepo["full_name"]] = $gRepo;
		$cols = GithubRepository::getColumns();
		$gRepo['contributors'] = $this->getContributorNames($gRepo['name']);
		$gRepo['contributed'] = in_array($this->getConnectorUserName(), $gRepo['contributors']);
		$gRepo['user_id'] = $this->getUserId();
		foreach($gRepo as $key => $value){
			if(!in_array($key, $cols)){
				QMLog::once("Removing $key from repo because it doesn't exist on model ".GithubRepository::class);
				unset($gRepo[$key]);
			}
		}
		$lRepo = GithubRepository::updateOrCreate(['id' => $gRepo['id']], $gRepo);
		$this->logDebug("Got $lRepo->full_name repo");
		$this->setRepo($lRepo);
		return $lRepo;
	}
	/**
	 * @param GithubRepository $r
	 */
	public function setRepo(GithubRepository $r): void {
		$this->repositories[$r->full_name] = $r;
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getAuthorizationMethod(): int{
		return AbstractService::AUTHORIZATION_METHOD_HEADER_BEARER;
	}
	/**
	 * @return string|null
	 */
	public function getUserProfilePageUrl(): ?string{
		$name = $this->getConnectorUserName();
		if(!$name){
			le("no name!");
		}
		return "https://github.com/$name";
	}
	private function nextPage(): ?int {
		$nextPage = $this->getPage("next");
		return $nextPage;
	}
	private function lastPage(): ?int {
		return $this->getPage("last");
	}
	private function getPage(string $type): ?int {
		$lastResponse = $this->getLastResponse();
		$linkHeader = $lastResponse->getHeader("Link");
        if(!$linkHeader){
            return null;
        }
		$nextPage = QMStr::between($linkHeader[0], "page=", '>; rel="'.$type);
		if(!$nextPage){return null;}
		$nextPage = QMStr::after("page=", $nextPage, $nextPage);
		$nextPage = QMStr::after( "page=", $nextPage,$nextPage);
		return $nextPage;
	}
	/**
	 * @param $commitObject
	 * @throws \App\DataSources\TooEarlyException
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	private function addCommitMeasurement($commitObject): void{
		$v = $this->getCommitsUserVariable();
		$timestamp = strtotime($commitObject->commit->author->date);
		$note = new AdditionalMetaData();
		$note->image = $this->currentRepo->getImage();
		$note->url = $commitObject->html_url;
		$note->message = $commitObject->commit->message;
		$this->addMeasurement($v->getVariableName(), $timestamp, 1, EventUnit::NAME, $v->getVariableCategoryName(), [], null,
		$note);
	}
	/**
	 * @param array $params
	 * @param string|null $repoName
	 * @return array
	 */
	private function repoCommits(array $params = [], string $repoName = null): array {
		$c = $this->commits()
			->all($this->getConnectorUserName(), $repoName ?? $this->getCurrentRepositoryName(), $params);
		return $c;
	}
	/**
	 * @param int|null $currentFromTime
	 * @return bool
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	public function weShouldBreak(int $currentFromTime = null): bool{
		$v = $this->getCommitsUserVariable();
		if(!$v->getNewMeasurements()){
			$this->logInfo("Not breaking yet because we haven't gotten to a repo with commits in time range");
			return false;
		}
		return parent::weShouldBreak();
	}
	public function commits(): Commits{
		$c = new Commits($this->githubClient());
		return $c;
	}
	/**
	 * @return \Github\Client
	 */
	private function githubClient(): Client{
		$builder = new Builder();
		$builder->addHeaderValue('Accept', 'application/vnd.github.squirrel-girl-preview');
		$github = new Client($builder);
		try {
			$token = $this->getAccessTokenString();
			$github->authenticate($token, null, Client::AUTH_ACCESS_TOKEN);
			return $github;
		} catch (TokenNotFoundException $e) {
			le($e);
		}
	}
	/**
	 * @return string
	 */
	public function urlUserDetails(): string{
		return $this->getUrlForPath(self::PATH_USER);
	}
	/**
	 * @return void
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 */
	private function getAllCommitsForSingleRepository(): void{
		$repositoryName = $this->getCurrentRepositoryName();
		$author = $this->getConnectorUserName();
		$latestAt = $this->getOrCalculateLatestMeasurementAt();
		$fromAt = $this->getFromAt();
		if($latestAt && strtotime($latestAt) > $fromAt){
			$fromAt = $latestAt;
		}
		$nextPage = 1;
		while($nextPage){
			if($this->weShouldBreak()){
				break;
			}
			$url = $this->getUrlForPath(self::PATH_REPOS . "/$repositoryName/commits", [
				'author' => $author,
				'page' => $nextPage
				// Just use the default 'per_page' => self::MAX_PER_PAGE,
				//"since" => $fromAt // For some reason since doesn't work anymore
			]);
			try {
				$commitArray = $this->request($url);
				if(is_string($commitArray)){$commitArray = QMArr::toArray($commitArray);}
				$nextPage = $this->nextPage();
				$lastPage = $this->lastPage();
				$this->logInfo("Getting page $nextPage out of $lastPage commit pages...");
			} catch (\Exception $e) {
				if($e->getCode() === 409){ // Repository has no commits
					$this->logInfo(__METHOD__.": ".$e->getMessage());
					return;
				}
				throw $e;
			}
			$statusCode = $this->getLastStatusCode();
			switch($statusCode) {
				case 200:
					$justGot = count($commitArray);
					$this->logInfoWithoutContext("Got $justGot commits for $repositoryName");
					try {
						$this->saveCommits($fromAt, $commitArray);
					} catch (TooEarlyException $e) {
						$this->logInfo("Not getting any more commits for $repositoryName because " . $e->getMessage());
						return;
					}
					break;
				case 404:
					$this->logInfo("Repository not found", [$commitArray]);
					break;
				default:
					$this->handleUnsuccessfulResponses($commitArray);
					break;
			}
		}
	}
	/**
	 * @return mixed
	 */
	private function getAllowedRepositories(): array{
		$meta = $this->getConnection()->meta;
		if(!$meta){
			return [];
		}
		return $meta['repositories'] ?? [];
	}
	private function inAllowedRepos(string $repo): bool{
		$allowed = $this->getAllowedRepositories();
		if(!$allowed){
			return true;
		}
		return in_array($repo, $allowed);
	}
	/**
	 * @return Repo[]
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	public function getRepositories(): array{
		if($this->repositories){return $this->repositories;}
		$this->logConnectUrl();
		$newRepos = $this->fetchRepos();
		foreach($newRepos as $gRepo){$this->saveRepo($gRepo);}
		ksort($this->repositories);
		asort($this->contributions);
		QMLog::var_export($this->contributions, true, true);
		return $this->repositories;
	}
	public function getDefaultVariableCategoryName(): string{
		return $this->defaultVariableCategoryName = GoalsVariableCategory::NAME;
	}
	/**
	 * @return string
	 */
	private function getCurrentRepositoryName(): string{
		return $this->currentRepo->full_name;
	}
	/**
	 * @param string $fromAt
	 * @param array $commitArray
	 * @throws \App\DataSources\TooEarlyException
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	private function saveCommits(string $fromAt, array $commitArray){
		if(!count($commitArray)){
			$this->logDebug('No commits found since ' . $fromAt . ' for repo ' . $this->getCurrentRepositoryName());
		}
		$byDate = [];
		foreach($commitArray as $commitObject){
			$commitObject = json_decode(json_encode($commitObject));
			$date = $commitObject->commit->author->date;
			$dupe = $byDate[$date] ?? null;
			if($dupe){
				ConsoleLog::warning("duplicate at $date: 
				Current: ".QMStr::toString($commitObject->commit->message)."
				Duplicate: ".QMStr::toString($dupe->commit->message), $commitObject);
			}
			$byDate[$date] = $commitObject;
		}
		foreach($byDate as $date => $commitObject){
			$user = $commitObject->author->login;
			if($user !== $this->getConnectorUserName()){
				$this->logInfo("Skipping commit $commitObject {$commitObject->commit->message} because user is $user");
				continue;
			}
			try {
				$this->addCommitMeasurement($commitObject);
			} catch (TooEarlyException $e) {
				$this->logInfo(__METHOD__.": ".$e->getMessage());
			}
		}
		if(isset($e)){
			throw $e;
		}
	}
	/**
	 * @return QMUserVariable
	 */
	private function getCommitsUserVariable(): QMUserVariable{
		$uv = $this->currentRepo->getUserVariable($this->getUserId());
		$this->qmUserVariables[$uv->getVariableName()] = $uv;
		return $this->setUserVariableByName($uv->getQMUserVariable());
	}
	public function getQMUserVariable(string $variableName, string $defaultUnitName = null,
		string $variableCategoryName = null, array $params = []): ?QMUserVariable{
		le("use getCommitsUserVariable for $this->name");
		//return parent::getQMUserVariable($variableName, $defaultUnitName, $variableCategoryName, $params);
	}
	/**
	 * @param bool $throwException
	 * @return string|null
	 */
	public function getConnectorUserName(bool $throwException = false): ?string{
		$meta = $this->getUserMeta();
		if(isset($meta["github_login"])){return $meta["github_login"];}
		$name = parent::getConnectorUserName($throwException);
		if($name !== "mikepsinn" && $this->userId === 230 && AppMode::isProduction()){
			le("Returning mikepsinn instead of $name because I guess it's stuck there from testing");
		}
		return $name;
	}
	/**
	 * {@inheritdoc}
	 */
	protected function parseAccessTokenResponse($responseBody): StdOAuth2Token{
		return parent::parseNeverExpiresAccessTokenResponse($responseBody);
	}
	/**
	 * Used to configure response type -- we want JSON from GitHub, default is query string format
	 * @return array
	 */
	protected function getExtraOAuthHeaders(): array{
		return ['Accept' => 'application/json'];
	}
	/**
	 * Required for GitHub API calls.
	 * @return array
	 */
	protected function getExtraApiHeaders(): array{
		return ['Accept' => 'application/vnd.github.beta+json'];
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getScopesDelimiter(): string{
		return ',';
	}

    /**
     * @param string $query
     * @param int $limit
     * @param int $page
     * @return Collection|GithubRepository[]
     */
    public static function search(string $query, int $limit = 10, int $page = 1): array|Collection{
		return GithubRepository::whereLike(GithubRepository::FIELD_NAME, $query)
            ->limit($limit)
            ->offset(($page - 1) * $limit)
            ->get();
	}
	private function getEvents(){
		$client = $this->githubClient();
		$userApi = $client->api('user');
		$paginator  = new \Github\ResultPager($client);
		$this->logInfo("Getting events for ".$this->getConnectorUserName());
		$events = $paginator->fetchAll($userApi, 'events', ['username' => $this->getConnectorUserName()]);
		return $events;
	}
	protected function getContributors(string $repo): array{
		$github = $this->githubClient();
		$connectorUserName = $this->getConnectorUserName();
		try {
			$contributors = $github->repo()->contributors($connectorUserName, $repo);
		} catch (\Throwable $e){
		    QMLog::info("Error getting contributors for $repo: ".$e->getMessage());
			$this->contributions[$repo] = 'unknown';
			return [];
		}
		if(!is_array($contributors)){
			$this->logInfo("contributors for $repo is not an array but is: $contributors");
			$this->contributions[$repo] = 'unknown';
			return [];
		}
		foreach($contributors as $contributor){
			if($contributor['login'] === $connectorUserName){
				$contributions = $contributor['contributions'];
				$this->contributions[$repo] = $contributions;
				$this->logInfo("$connectorUserName made $contributions contribution to $repo");
			}
		}
		return $contributors;
	}
	protected function getContributorNames(string $repo): array{
		$contributors = $this->getContributors($repo);
		return collect($contributors)->pluck('login')->toArray();
	}
	public function deleteRepositories(array $repos){
		$github = $this->githubClient();
		foreach($repos as $repo){
			$this->logInfo("Deleting $repo");
			$github->repo()->remove($this->getConnectorUserName(), $repo);
		}
	}
	/**
	 * @param $name
	 * @return GithubRepository
	 * @throws ConnectorException
	 */
	public function getRepo(string $name): GithubRepository{
		$owner = $this->getConnectorUserName();
		if(str_contains($name, '/')){
			[$owner, $name] = explode('/', $name);
		}
		$fullName = "$owner/$name";
		if(isset($this->repositories[$fullName])){
			return $this->repositories[$fullName];
		}
		if($db = GithubRepository::whereName($name)->where(GithubRepository::FIELD_OWNER, $owner)->first()){
			$this->setRepo($db);
			return $db; 
		}
		$github = $this->githubClient();
		$repo = $github->repo()->show($owner, $name);
		return $this->saveRepo($repo);
	}
}
