<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors\Responses\Github;
use App\Buttons\QMButton;
use App\DataSources\Connectors\GithubConnector;
use App\DataSources\Connectors\Responses\BaseResponseObject;
use App\Exceptions\ModelValidationException;
use App\Models\UserTag;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Types\QMStr;
use App\Variables\CommonVariables\GoalsCommonVariables\CodeCommitsCommonVariable;
class Repo extends BaseResponseObject {
	/**
	 * @var int
	 * @example 21549045
	 */
	public $id;
	/**
	 * @var string
	 * @example MDEwOlJlcG9zaXRvcnkyMTU0OTA0NQ==
	 */
	public $node_id;
	/**
	 * @var string
	 * @example Abolitionist-Project-WP
	 */
	public $name;
	/**
	 * @var string
	 * @example Abolitionist-Project/Abolitionist-Project-WP
	 */
	public $full_name;
	/**
	 * @var bool
	 */
	public $private;
	/**
	 * @var Owner
	 */
	public $owner;
	/**
	 * @var string
	 * @example https://github.com/Abolitionist-Project/Abolitionist-Project-WP
	 */
	public $html_url;
	/**
	 * @var string
	 * @example This site contains all information about the Abolitionist Project.
	 */
	public $description;
	/**
	 * @var bool
	 */
	public $fork;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP
	 */
	public $url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/forks
	 */
	public $forks_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/keys{/key_id}
	 */
	public $keys_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/collaborators{/collaborator}
	 */
	public $collaborators_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/teams
	 */
	public $teams_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/hooks
	 */
	public $hooks_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/issues/events{/number}
	 */
	public $issue_events_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/events
	 */
	public $events_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/assignees{/user}
	 */
	public $assignees_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/branches{/branch}
	 */
	public $branches_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/tags
	 */
	public $tags_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/git/blobs{/sha}
	 */
	public $blobs_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/git/tags{/sha}
	 */
	public $git_tags_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/git/refs{/sha}
	 */
	public $git_refs_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/git/trees{/sha}
	 */
	public $trees_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/statuses/{sha}
	 */
	public $statuses_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/languages
	 */
	public $languages_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/stargazers
	 */
	public $stargazers_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/contributors
	 */
	public $contributors_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/subscribers
	 */
	public $subscribers_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/subscription
	 */
	public $subscription_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/commits{/sha}
	 */
	public $commits_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/git/commits{/sha}
	 */
	public $git_commits_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/comments{/number}
	 */
	public $comments_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/issues/comments{/number}
	 */
	public $issue_comment_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/contents/{+path}
	 */
	public $contents_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/compare/{base}...{head}
	 */
	public $compare_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/merges
	 */
	public $merges_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/{archive_format}{/ref}
	 */
	public $archive_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/downloads
	 */
	public $downloads_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/issues{/number}
	 */
	public $issues_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/pulls{/number}
	 */
	public $pulls_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/milestones{/number}
	 */
	public $milestones_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/notifications{?since,all,participating}
	 */
	public $notifications_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/labels{/name}
	 */
	public $labels_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/releases{/id}
	 */
	public $releases_url;
	/**
	 * @var string
	 * @example https://api.github.com/repos/Abolitionist-Project/Abolitionist-Project-WP/deployments
	 */
	public $deployments_url;
	/**
	 * @var string
	 * @example 2014-07-06T21:33:22Z
	 */
	public $created_at;
	/**
	 * @var string
	 * @example 2014-08-08T00:34:10Z
	 */
	public $updated_at;
	/**
	 * @var string
	 * @example 2014-08-08T00:32:36Z
	 */
	public $pushed_at;
	/**
	 * @var string
	 * @example git://github.com/Abolitionist-Project/Abolitionist-Project-WP.git
	 */
	public $git_url;
	/**
	 * @var string
	 * @example git@github.com:Abolitionist-Project/Abolitionist-Project-WP.git
	 */
	public $ssh_url;
	/**
	 * @var string
	 * @example https://github.com/Abolitionist-Project/Abolitionist-Project-WP.git
	 */
	public $clone_url;
	/**
	 * @var string
	 * @example https://github.com/Abolitionist-Project/Abolitionist-Project-WP
	 */
	public $svn_url;
	/**
	 * @var string
	 */
	public $homepage;
	/**
	 * @var int
	 * @example 17735
	 */
	public $size;
	/**
	 * @var int
	 */
	public $stargazers_count;
	/**
	 * @var int
	 */
	public $watchers_count;
	/**
	 * @var string
	 * @example JavaScript
	 */
	public $language;
	/**
	 * @var bool
	 */
	public $has_issues;
	/**
	 * @var bool
	 */
	public $has_projects;
	/**
	 * @var bool
	 */
	public $has_downloads;
	/**
	 * @var bool
	 */
	public $has_wiki;
	/**
	 * @var bool
	 */
	public $has_pages;
	/**
	 * @var int
	 * @example 1
	 */
	public $forks_count;
	/**
	 * @var string
	 */
	public $mirror_url;
	/**
	 * @var bool
	 */
	public $archived;
	/**
	 * @var bool
	 */
	public $disabled;
	/**
	 * @var int
	 * @example 1
	 */
	public $open_issues_count;
	/**
	 * @var string
	 */
	public $license;
	/**
	 * @var int
	 * @example 1
	 */
	public $forks;
	/**
	 * @var int
	 * @example 1
	 */
	public $open_issues;
	/**
	 * @var int
	 */
	public $watchers;
	/**
	 * @var string
	 * @example master
	 */
	public $default_branch;
	/**
	 * @var string
	 * @example master
	 */
	public $master_branch;
	/**
	 * @var Permissions
	 */
	public $permissions;
	private array $userVariables = [];
	/**
	 * @var Variable|Variable[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
	 */
	private $variable;
	/**
	 * @param null $data
	 */
	public function __construct($data = null){
		if(!$data){
			return;
		}
		parent::__construct($data);
		$this->owner = new Owner($this->owner);
		$this->permissions = new Permissions($this->permissions);
	}
	public function getHtml(): string{
		$button = $this->getButton();
		return $button->getRoundOutlineWithIcon();
	}
	public function getButton(): QMButton{
		$button = new QMButton();
		$button->setTextAndTitle(QMStr::titleCaseSlow(str_replace("-", " ", $this->name)));
		$button->setUrl($this->url);
		if($this->description){
			$button->setTooltip($this->getSubtitleAttribute());
		}
		$button->setId($this->name);
		$owner = $this->owner;
		$button->setImage($owner->avatar_url);
		return $button;
	}
	/**
	 * @param $key
	 * @return mixed
	 */
	public function getAttribute($key){
		return $this->$key;
	}
	public function getDisplayNameAttribute(): string{
		return $this->name;
	}
	public static function getUniqueIndexColumns(): array{
		return ['name', 'owner'];
	}
	public function getId(): string{
		return $this->name;
	}
	static public function getTableName(): string{
		return "repos";
	}
	public function getUrl(): string{
		return $this->html_url;
	}
	/**
	 * @return string
	 */
	public function getSubtitleAttribute(): string{
		$desc = QMStr::after(":", $this->description, $this->description);
		$desc = QMStr::after("»", $desc, $desc);
		return $desc;
	}
	public function getOwner(): Owner{
		return new Owner($this->owner);
	}
	public function getPermissions(): Permissions{
		return new Permissions($this->permissions);
	}
	public function getImage(): string{
		return $this->getOwner()->avatar_url;
	}
	public function getVariable(int $userId): Variable{
		if($this->variable){
			return $this->variable;
		}
		if($v = Variable::findByName($this->getVariableName())){
			return $this->variable = $v;
		}
		$commitsV = Variable::findInMemoryOrDB(CodeCommitsCommonVariable::ID);
		$newV = $commitsV->clone([
			Variable::FIELD_NAME => $this->getVariableName(),
			Variable::FIELD_CREATOR_USER_ID => $userId,
			Variable::FIELD_CLIENT_ID => GithubConnector::NAME,
			Variable::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 1,
		]);
		$newV->image_url = $this->getImage();
		$newV->informational_url = $this->getUrl();
		$newV->additional_meta_data = $this->toArray();
		try {
			$newV->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		return $this->variable = $newV;
	}
	protected function getVariableName(): ?string{
		return "Github Code Commits to $this->full_name";
	}
	/**
	 * @return array
	 */
	private function toArray(): array{
		return json_decode(json_encode($this), true);
	}
	public function getUserVariable(int $userId): UserVariable{
		$uv = $this->getVariable($userId)->getOrCreateUserVariable($userId);
		if(!isset($this->userVariables[$uv->getVariableName()])){
			$this->userVariables[$uv->getVariableName()] = $uv;
			$tag = new UserTag;
			$tag->setTaggedUserVariable($uv);
			$main = CodeCommitsCommonVariable::instance()->getUserVariable($userId);
			$tag->setTagUserVariable($main);
			$tag->save();
		}
		return $uv;
	}
}
