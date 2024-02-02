<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;

use App\DataSources\Connectors\GithubConnector;
use App\Exceptions\ModelValidationException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\Base\BaseGithubRepository;
use App\Properties\User\UserIdProperty;
use App\Repos\GitRepo;
use App\Types\MySQLTypes;
use App\Types\QMStr;
use App\Variables\CommonVariables\GoalsCommonVariables\CodeCommitsCommonVariable;
use Github\Api\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
/**
 * \App\Models\GithubRepository
 *
 * @property int $id Automatically generated unique id for the github repository
 * @property string|null $client_id The ID for the API client that created the record
 * @property \Illuminate\Support\Carbon $created_at The time the record was originally created
 * @property \Illuminate\Support\Carbon|null $deleted_at The time the record was deleted
 * @property \Illuminate\Support\Carbon $updated_at The time the record was last modified
 * @property int $user_id The user ID for the owner of the record
 * @property int $github_repository_id Github repository id Example: 158861117
 * @property string $node_id Example: MDEwOlJlcG9zaXRvcnkxNTg4NjExMTc=
 * @property string $name Example: qm-api
 * @property string $full_name Example: mikepsinn/qm-api
 * @property bool $private Example: 1
 * @property string $owner Example: {login:mikepsinn,id:2808553,node_id:MDQ6VXNlcjI4MDg1NTM=,avatar_url:https://avatars.githubusercontent.com/u/2808553?v=4,gravatar_id:,url:https://api.github.com/users/mikepsinn,html_url:https://github.com/mikepsinn,followers_url:https://api.github.com/users/mikepsinn/followers,following_url:https://api.github.com/users/mikepsinn/following{/other_user},gists_url:https://api.github.com/users/mikepsinn/gists{/gist_id},starred_url:https://api.github.com/users/mikepsinn/starred{/owner}{/repo},subscriptions_url:https://api.github.com/users/mikepsinn/subscriptions,organizations_url:https://api.github.com/users/mikepsinn/orgs,repos_url:https://api.github.com/users/mikepsinn/repos,events_url:https://api.github.com/users/mikepsinn/events{/privacy},received_events_url:https://api.github.com/users/mikepsinn/received_events,type:User,site_admin:false}
 * @property string $html_url Example: https://github.com/mikepsinn/qm-api
 * @property string $description Example: I'm a description
 * @property bool $fork Example:
 * @property string $url Example: https://api.github.com/repos/mikepsinn/qm-api
 * @property string $forks_url Example: https://api.github.com/repos/mikepsinn/qm-api/forks
 * @property string $keys_url Example: https://api.github.com/repos/mikepsinn/qm-api/keys{/key_id}
 * @property string $collaborators_url Example: https://api.github.com/repos/mikepsinn/qm-api/collaborators{/collaborator}
 * @property string $teams_url Example: https://api.github.com/repos/mikepsinn/qm-api/teams
 * @property string $hooks_url Example: https://api.github.com/repos/mikepsinn/qm-api/hooks
 * @property string $issue_events_url Example: https://api.github.com/repos/mikepsinn/qm-api/issues/events{/number}
 * @property string $events_url Example: https://api.github.com/repos/mikepsinn/qm-api/events
 * @property string $assignees_url Example: https://api.github.com/repos/mikepsinn/qm-api/assignees{/user}
 * @property string $branches_url Example: https://api.github.com/repos/mikepsinn/qm-api/branches{/branch}
 * @property string $tags_url Example: https://api.github.com/repos/mikepsinn/qm-api/tags
 * @property string $blobs_url Example: https://api.github.com/repos/mikepsinn/qm-api/git/blobs{/sha}
 * @property string $git_tags_url Example: https://api.github.com/repos/mikepsinn/qm-api/git/tags{/sha}
 * @property string $git_refs_url Example: https://api.github.com/repos/mikepsinn/qm-api/git/refs{/sha}
 * @property string $trees_url Example: https://api.github.com/repos/mikepsinn/qm-api/git/trees{/sha}
 * @property string $statuses_url Example: https://api.github.com/repos/mikepsinn/qm-api/statuses/{sha}
 * @property string $languages_url Example: https://api.github.com/repos/mikepsinn/qm-api/languages
 * @property string $stargazers_url Example: https://api.github.com/repos/mikepsinn/qm-api/stargazers
 * @property string $contributors_url Example: https://api.github.com/repos/mikepsinn/qm-api/contributors
 * @property string $subscribers_url Example: https://api.github.com/repos/mikepsinn/qm-api/subscribers
 * @property string $subscription_url Example: https://api.github.com/repos/mikepsinn/qm-api/subscription
 * @property string $commits_url Example: https://api.github.com/repos/mikepsinn/qm-api/commits{/sha}
 * @property string $git_commits_url Example: https://api.github.com/repos/mikepsinn/qm-api/git/commits{/sha}
 * @property string $comments_url Example: https://api.github.com/repos/mikepsinn/qm-api/comments{/number}
 * @property string $issue_comment_url Example: https://api.github.com/repos/mikepsinn/qm-api/issues/comments{/number}
 * @property string $contents_url Example: https://api.github.com/repos/mikepsinn/qm-api/contents/{+path}
 * @property string $compare_url Example: https://api.github.com/repos/mikepsinn/qm-api/compare/{base}...{head}
 * @property string $merges_url Example: https://api.github.com/repos/mikepsinn/qm-api/merges
 * @property string $archive_url Example: https://api.github.com/repos/mikepsinn/qm-api/{archive_format}{/ref}
 * @property string $downloads_url Example: https://api.github.com/repos/mikepsinn/qm-api/downloads
 * @property string $issues_url Example: https://api.github.com/repos/mikepsinn/qm-api/issues{/number}
 * @property string $pulls_url Example: https://api.github.com/repos/mikepsinn/qm-api/pulls{/number}
 * @property string $milestones_url Example: https://api.github.com/repos/mikepsinn/qm-api/milestones{/number}
 * @property string $notifications_url Example: https://api.github.com/repos/mikepsinn/qm-api/notifications{?since,all,participating}
 * @property string $labels_url Example: https://api.github.com/repos/mikepsinn/qm-api/labels{/name}
 * @property string $releases_url Example: https://api.github.com/repos/mikepsinn/qm-api/releases{/id}
 * @property string $deployments_url Example: https://api.github.com/repos/mikepsinn/qm-api/deployments
 * @property string $pushed_at Example: 2021-10-24T00:07:07Z
 * @property string $git_url Example: git://github.com/mikepsinn/qm-api.git
 * @property string $ssh_url Example: git@github.com:mikepsinn/qm-api.git
 * @property string $clone_url Example: https://github.com/mikepsinn/qm-api.git
 * @property string $svn_url Example: https://github.com/mikepsinn/qm-api
 * @property string $homepage Example: https://quantimo.do
 * @property int $size Example: 178451
 * @property int $stargazers_count Example: 2
 * @property int $watchers_count Example: 2
 * @property string $language Example: PHP
 * @property bool $has_issues Example: 1
 * @property bool $has_projects Example: 1
 * @property bool $has_downloads Example: 1
 * @property bool $has_wiki Example: 1
 * @property bool $has_pages Example:
 * @property int $forks_count Example: 0
 * @property bool $archived Example:
 * @property bool $disabled Example:
 * @property int $open_issues_count Example: 89
 * @property bool $allow_forking Example: 1
 * @property bool $is_template Example:
 * @property string $topics Example: [digital-health,health,healthcare-data]
 * @property string $visibility Example: private
 * @property int $forks Example: 0
 * @property int $open_issues Example: 89
 * @property int $watchers Example: 2
 * @property string $default_branch Example: develop
 * @property string $permissions Example: {admin:true,maintain:true,push:true,triage:true,pull:true}
 * @property string $temp_clone_token Example: AAVNV2SECRETYTBOS7ZI
 * @property bool $allow_squash_merge Example: 1
 * @property bool $allow_merge_commit Example: 1
 * @property bool $allow_rebase_merge Example: 1
 * @property bool $allow_auto_merge Example:
 * @property bool $delete_branch_on_merge Example:
 * @property int $network_count Example: 0
 * @property int $subscribers_count Example: 0
 * @property-read \App\Models\OAClient|null $client
 * @property mixed|null $raw
 * @property-read string $report_title
 * @property-read array|mixed|string|string[]|null $rule_for
 * @property-read array $rules_for
 * @property-read string $subtitle
 * @property-read string $title
 * @property-read \App\Models\OAClient|null $oa_client
 * @property-read \App\Models\User $wp_user
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|GithubRepository newModelQuery()
 * @method static Builder|GithubRepository newQuery()
 * @method static Builder|GithubRepository query()
 * @method static Builder|GithubRepository whereAllowAutoMerge($value)
 * @method static Builder|GithubRepository whereAllowForking($value)
 * @method static Builder|GithubRepository whereAllowMergeCommit($value)
 * @method static Builder|GithubRepository whereAllowRebaseMerge($value)
 * @method static Builder|GithubRepository whereAllowSquashMerge($value)
 * @method static Builder|GithubRepository whereArchiveUrl($value)
 * @method static Builder|GithubRepository whereArchived($value)
 * @method static Builder|GithubRepository whereAssigneesUrl($value)
 * @method static Builder|GithubRepository whereBlobsUrl($value)
 * @method static Builder|GithubRepository whereBranchesUrl($value)
 * @method static Builder|GithubRepository whereClientId($value)
 * @method static Builder|GithubRepository whereCloneUrl($value)
 * @method static Builder|GithubRepository whereCollaboratorsUrl($value)
 * @method static Builder|GithubRepository whereCommentsUrl($value)
 * @method static Builder|GithubRepository whereCommitsUrl($value)
 * @method static Builder|GithubRepository whereCompareUrl($value)
 * @method static Builder|GithubRepository whereContentsUrl($value)
 * @method static Builder|GithubRepository whereContributorsUrl($value)
 * @method static Builder|GithubRepository whereCreatedAt($value)
 * @method static Builder|GithubRepository whereDefaultBranch($value)
 * @method static Builder|GithubRepository whereDeleteBranchOnMerge($value)
 * @method static Builder|GithubRepository whereDeletedAt($value)
 * @method static Builder|GithubRepository whereDeploymentsUrl($value)
 * @method static Builder|GithubRepository whereDescription($value)
 * @method static Builder|GithubRepository whereDisabled($value)
 * @method static Builder|GithubRepository whereDownloadsUrl($value)
 * @method static Builder|GithubRepository whereEventsUrl($value)
 * @method static Builder|GithubRepository whereFork($value)
 * @method static Builder|GithubRepository whereForks($value)
 * @method static Builder|GithubRepository whereForksCount($value)
 * @method static Builder|GithubRepository whereForksUrl($value)
 * @method static Builder|GithubRepository whereFullName($value)
 * @method static Builder|GithubRepository whereGitCommitsUrl($value)
 * @method static Builder|GithubRepository whereGitRefsUrl($value)
 * @method static Builder|GithubRepository whereGitTagsUrl($value)
 * @method static Builder|GithubRepository whereGitUrl($value)
 * @method static Builder|GithubRepository whereGithubRepositoryId($value)
 * @method static Builder|GithubRepository whereHasDownloads($value)
 * @method static Builder|GithubRepository whereHasIssues($value)
 * @method static Builder|GithubRepository whereHasPages($value)
 * @method static Builder|GithubRepository whereHasProjects($value)
 * @method static Builder|GithubRepository whereHasWiki($value)
 * @method static Builder|GithubRepository whereHomepage($value)
 * @method static Builder|GithubRepository whereHooksUrl($value)
 * @method static Builder|GithubRepository whereHtmlUrl($value)
 * @method static Builder|GithubRepository whereId($value)
 * @method static Builder|GithubRepository whereIsTemplate($value)
 * @method static Builder|GithubRepository whereIssueCommentUrl($value)
 * @method static Builder|GithubRepository whereIssueEventsUrl($value)
 * @method static Builder|GithubRepository whereIssuesUrl($value)
 * @method static Builder|GithubRepository whereKeysUrl($value)
 * @method static Builder|GithubRepository whereLabelsUrl($value)
 * @method static Builder|GithubRepository whereLanguage($value)
 * @method static Builder|GithubRepository whereLanguagesUrl($value)
 * @method static Builder|GithubRepository whereMergesUrl($value)
 * @method static Builder|GithubRepository whereMilestonesUrl($value)
 * @method static Builder|GithubRepository whereName($value)
 * @method static Builder|GithubRepository whereNetworkCount($value)
 * @method static Builder|GithubRepository whereNodeId($value)
 * @method static Builder|GithubRepository whereNotificationsUrl($value)
 * @method static Builder|GithubRepository whereOpenIssues($value)
 * @method static Builder|GithubRepository whereOpenIssuesCount($value)
 * @method static Builder|GithubRepository whereOwner($value)
 * @method static Builder|GithubRepository wherePermissions($value)
 * @method static Builder|GithubRepository wherePrivate($value)
 * @method static Builder|GithubRepository wherePullsUrl($value)
 * @method static Builder|GithubRepository wherePushedAt($value)
 * @method static Builder|GithubRepository whereReleasesUrl($value)
 * @method static Builder|GithubRepository whereSize($value)
 * @method static Builder|GithubRepository whereSshUrl($value)
 * @method static Builder|GithubRepository whereStargazersCount($value)
 * @method static Builder|GithubRepository whereStargazersUrl($value)
 * @method static Builder|GithubRepository whereStatusesUrl($value)
 * @method static Builder|GithubRepository whereSubscribersCount($value)
 * @method static Builder|GithubRepository whereSubscribersUrl($value)
 * @method static Builder|GithubRepository whereSubscriptionUrl($value)
 * @method static Builder|GithubRepository whereSvnUrl($value)
 * @method static Builder|GithubRepository whereTagsUrl($value)
 * @method static Builder|GithubRepository whereTeamsUrl($value)
 * @method static Builder|GithubRepository whereTempCloneToken($value)
 * @method static Builder|GithubRepository whereTopics($value)
 * @method static Builder|GithubRepository whereTreesUrl($value)
 * @method static Builder|GithubRepository whereUpdatedAt($value)
 * @method static Builder|GithubRepository whereUrl($value)
 * @method static Builder|GithubRepository whereUserId($value)
 * @method static Builder|GithubRepository whereVisibility($value)
 * @method static Builder|GithubRepository whereWatchers($value)
 * @method static Builder|GithubRepository whereWatchersCount($value)
 * @property string|null $mirror_url
 * @property array|null $license
 * @property int|null $web_commit_signoff_required
 * @property mixed|null $calculated
 * @property-read array $invalid_record_for
 * @method static Builder|GithubRepository onlyTrashed()
 * @method static Builder|GithubRepository whereLicense($value)
 * @method static Builder|GithubRepository whereMirrorUrl($value)
 * @method static Builder|GithubRepository whereWebCommitSignoffRequired($value)
 * @method static Builder|GithubRepository withTrashed()
 * @method static Builder|GithubRepository withoutTrashed()
 * @mixin \Eloquent
 */
class GithubRepository extends BaseGithubRepository
{
	private static array $alreadyComplainedByAttribute = [];
	protected $hidden = [
		self::FIELD_TEMP_CLONE_TOKEN
	];
	protected $guarded = [];
	protected array $rules = [
		self::FIELD_ALLOW_AUTO_MERGE => 'boolean',
		self::FIELD_ALLOW_FORKING => 'boolean',
		self::FIELD_ALLOW_MERGE_COMMIT => 'boolean',
		self::FIELD_ALLOW_REBASE_MERGE => 'boolean',
		self::FIELD_ALLOW_SQUASH_MERGE => 'boolean',
		self::FIELD_ARCHIVED => 'boolean',
		self::FIELD_ARCHIVE_URL => 'max:255',
		self::FIELD_ASSIGNEES_URL => 'max:255',
		self::FIELD_BLOBS_URL => 'max:255',
		self::FIELD_BRANCHES_URL => 'max:255',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_CLONE_URL => 'required|max:255',
		self::FIELD_COLLABORATORS_URL => 'max:255',
		self::FIELD_COMMENTS_URL => 'max:255',
		self::FIELD_COMMITS_URL => 'max:255',
		self::FIELD_COMPARE_URL => 'max:255',
		self::FIELD_CONTENTS_URL => 'max:255',
		self::FIELD_CONTRIBUTORS_URL => 'max:255',
		self::FIELD_DEFAULT_BRANCH => 'max:255',
		self::FIELD_DELETE_BRANCH_ON_MERGE => 'boolean',
		self::FIELD_DEPLOYMENTS_URL => 'max:255',
		self::FIELD_DESCRIPTION => 'max:255',
		self::FIELD_DISABLED => 'boolean',
		self::FIELD_DOWNLOADS_URL => 'required|max:255',
		self::FIELD_EVENTS_URL => 'max:255',
		self::FIELD_FORK => 'boolean',
		self::FIELD_FORKS => 'integer|min:-2147483648|max:2147483647',
		self::FIELD_FORKS_COUNT => 'integer|min:-2147483648|max:2147483647',
		self::FIELD_FORKS_URL => 'required|max:255',
		self::FIELD_FULL_NAME => 'required|max:255',
		self::FIELD_GITHUB_REPOSITORY_ID => 'required|integer|min:-2147483648|max:2147483647|unique:github_repositories,github_repository_id',
		self::FIELD_GIT_COMMITS_URL => 'max:255',
		self::FIELD_GIT_REFS_URL => 'max:255',
		self::FIELD_GIT_TAGS_URL => 'max:255',
		self::FIELD_GIT_URL => 'required|max:255|unique:github_repositories,git_url',
		self::FIELD_HAS_DOWNLOADS => 'boolean',
		self::FIELD_HAS_ISSUES => 'boolean',
		self::FIELD_HAS_PAGES => 'boolean',
		self::FIELD_HAS_PROJECTS => 'boolean',
		self::FIELD_HAS_WIKI => 'boolean',
		self::FIELD_HOMEPAGE => 'max:255',
		self::FIELD_HOOKS_URL => 'max:255',
		self::FIELD_HTML_URL => 'max:255|unique:github_repositories,html_url',
		self::FIELD_ISSUES_URL => 'required|max:255',
		self::FIELD_ISSUE_COMMENT_URL => 'max:255',
		self::FIELD_ISSUE_EVENTS_URL => 'max:255',
		self::FIELD_IS_TEMPLATE => 'boolean',
		self::FIELD_KEYS_URL => 'max:255',
		self::FIELD_LABELS_URL => 'max:255',
		self::FIELD_LANGUAGE => 'max:255',
		self::FIELD_LANGUAGES_URL => 'max:255',
		self::FIELD_MERGES_URL => 'max:255',
		self::FIELD_MILESTONES_URL => 'max:255',
		self::FIELD_NAME => 'required|max:255|unique:github_repositories,name',
		self::FIELD_NETWORK_COUNT => 'integer|min:-2147483648|max:2147483647',
		self::FIELD_NODE_ID => 'max:255',
		self::FIELD_NOTIFICATIONS_URL => 'max:255',
		self::FIELD_OPEN_ISSUES => 'integer|min:-2147483648|max:2147483647',
		self::FIELD_OPEN_ISSUES_COUNT => 'integer|min:-2147483648|max:2147483647',
		self::FIELD_OWNER => 'required',
		self::FIELD_PERMISSIONS => 'required',
		self::FIELD_PRIVATE => 'required|boolean',
		self::FIELD_PULLS_URL => 'max:255',
		self::FIELD_PUSHED_AT => 'required|max:255',
		self::FIELD_RELEASES_URL => 'max:255',
		self::FIELD_SIZE => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_SSH_URL => 'max:255|unique:github_repositories,ssh_url',
		self::FIELD_STARGAZERS_COUNT => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_STARGAZERS_URL => 'required|max:255',
		self::FIELD_STATUSES_URL => 'max:255',
		self::FIELD_SUBSCRIBERS_COUNT => 'integer|min:-2147483648|max:2147483647',
		self::FIELD_SUBSCRIBERS_URL => 'max:255',
		self::FIELD_SUBSCRIPTION_URL => 'max:255',
		self::FIELD_SVN_URL => 'max:255',
		self::FIELD_TAGS_URL => 'required|max:255',
		self::FIELD_TEAMS_URL => 'max:255',
		self::FIELD_TEMP_CLONE_TOKEN => 'max:255',
		self::FIELD_TOPICS => 'required',
		self::FIELD_TREES_URL => 'max:255',
		self::FIELD_URL => 'required|max:255|unique:github_repositories,url',
		self::FIELD_USER_ID => 'numeric|min:0',
		self::FIELD_VISIBILITY => 'max:255',
		self::FIELD_WATCHERS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_WATCHERS_COUNT => 'required|integer|min:-2147483648|max:2147483647'
	];
	protected $casts = [
		self::FIELD_ALLOW_AUTO_MERGE => 'bool',
		self::FIELD_ALLOW_FORKING => 'bool',
		self::FIELD_ALLOW_MERGE_COMMIT => 'bool',
		self::FIELD_ALLOW_REBASE_MERGE => 'bool',
		self::FIELD_ALLOW_SQUASH_MERGE => 'bool',
		self::FIELD_ARCHIVED => 'bool',
		self::FIELD_ARCHIVE_URL => 'string',
		self::FIELD_ASSIGNEES_URL => 'string',
		self::FIELD_BLOBS_URL => 'string',
		self::FIELD_BRANCHES_URL => 'string',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CLONE_URL => 'string',
		self::FIELD_COLLABORATORS_URL => 'string',
		self::FIELD_COMMENTS_URL => 'string',
		self::FIELD_COMMITS_URL => 'string',
		self::FIELD_COMPARE_URL => 'string',
		self::FIELD_CONTENTS_URL => 'string',
		self::FIELD_CONTRIBUTORS_URL => 'string',
		self::FIELD_DEFAULT_BRANCH => 'string',
		self::FIELD_DELETE_BRANCH_ON_MERGE => 'bool',
		self::FIELD_DEPLOYMENTS_URL => 'string',
		self::FIELD_DESCRIPTION => 'string',
		self::FIELD_DISABLED => 'bool',
		self::FIELD_DOWNLOADS_URL => 'string',
		self::FIELD_EVENTS_URL => 'string',
		self::FIELD_FORK => 'bool',
		self::FIELD_FORKS => 'int',
		self::FIELD_FORKS_COUNT => 'int',
		self::FIELD_FORKS_URL => 'string',
		self::FIELD_FULL_NAME => 'string',
		self::FIELD_GITHUB_REPOSITORY_ID => 'int',
		self::FIELD_GIT_COMMITS_URL => 'string',
		self::FIELD_GIT_REFS_URL => 'string',
		self::FIELD_GIT_TAGS_URL => 'string',
		self::FIELD_GIT_URL => 'string',
		self::FIELD_HAS_DOWNLOADS => 'bool',
		self::FIELD_HAS_ISSUES => 'bool',
		self::FIELD_HAS_PAGES => 'bool',
		self::FIELD_HAS_PROJECTS => 'bool',
		self::FIELD_HAS_WIKI => 'bool',
		self::FIELD_HOMEPAGE => 'string',
		self::FIELD_HOOKS_URL => 'string',
		self::FIELD_HTML_URL => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_ISSUES_URL => 'string',
		self::FIELD_ISSUE_COMMENT_URL => 'string',
		self::FIELD_ISSUE_EVENTS_URL => 'string',
		self::FIELD_IS_TEMPLATE => 'bool',
		self::FIELD_KEYS_URL => 'string',
		self::FIELD_LABELS_URL => 'string',
		self::FIELD_LANGUAGE => 'string',
		self::FIELD_LANGUAGES_URL => 'string',
		self::FIELD_MERGES_URL => 'string',
		self::FIELD_MILESTONES_URL => 'string',
		self::FIELD_NAME => 'string',
		self::FIELD_NETWORK_COUNT => 'int',
		self::FIELD_NODE_ID => 'string',
		self::FIELD_NOTIFICATIONS_URL => 'string',
		self::FIELD_OPEN_ISSUES => 'int',
		self::FIELD_OPEN_ISSUES_COUNT => 'int',
		self::FIELD_OWNER => 'object',
		self::FIELD_PERMISSIONS => 'object',
		self::FIELD_PRIVATE => 'bool',
		self::FIELD_PULLS_URL => 'string',
		self::FIELD_PUSHED_AT => 'string',
		self::FIELD_RELEASES_URL => 'string',
		self::FIELD_SIZE => 'int',
		self::FIELD_SSH_URL => 'string',
		self::FIELD_STARGAZERS_COUNT => 'int',
		self::FIELD_STARGAZERS_URL => 'string',
		self::FIELD_STATUSES_URL => 'string',
		self::FIELD_SUBSCRIBERS_COUNT => 'int',
		self::FIELD_SUBSCRIBERS_URL => 'string',
		self::FIELD_SUBSCRIPTION_URL => 'string',
		self::FIELD_SVN_URL => 'string',
		self::FIELD_TAGS_URL => 'string',
		self::FIELD_TEAMS_URL => 'string',
		self::FIELD_TEMP_CLONE_TOKEN => 'string',
		self::FIELD_TOPICS => 'array',
		self::FIELD_TREES_URL => 'string',
		self::FIELD_URL => 'string',
		self::FIELD_USER_ID => 'int',
		self::FIELD_VISIBILITY => 'string',
		self::FIELD_WATCHERS => 'int',
		self::FIELD_WATCHERS_COUNT => 'int',
        self::FIELD_LICENSE => 'json'
	];

	/**
	 * @param array $attributes
	 * @return GithubRepository
	 */
	public function fill(array $attributes): GithubRepository{
		if(isset($attributes[self::FIELD_ID])){
			$attributes[GithubRepository::FIELD_GITHUB_REPOSITORY_ID] = $attributes[self::FIELD_ID];
		}
		foreach($attributes as $key => $value){
			if(!static::hasColumn($key)){
				if(isset(self::$alreadyComplainedByAttribute[$key])){
					continue;
				}
				ConsoleLog::info(__METHOD__.": no column named $key");
				$table = $this->getDBTable();
				$table->addColumn($key, MySQLTypes::typeNameForValue($value));
				$table->getCreateTableSQL();
				unset($attributes[$key]);
				self::$alreadyComplainedByAttribute[$key] = true;
			}
		}
		if($attributes && !isset($attributes[self::FIELD_USER_ID])){
			$attributes[self::FIELD_USER_ID] = UserIdProperty::USER_ID_SYSTEM;
		}
		if($attributes && isset($attributes[self::CREATED_AT])){
			$attributes[self::CREATED_AT] = db_date($attributes[self::CREATED_AT]);
		}
		if($attributes && isset($attributes[self::UPDATED_AT])){
			$attributes[self::UPDATED_AT] = db_date($attributes[self::UPDATED_AT]);
		}
		$res = parent::fill($attributes);
		return $res;
	}
	/**
	 * @param string $searchTerm
	 * @param $callback
	 * @return Collection|static[]
	 * @noinspection PhpReturnDocTypeMismatchInspection
	 */
	public static function search(string $searchTerm = '', $callback = null){
		$github = GitRepo::github();
		/** @var Search $api */
		$api = $github->api('search');
		$data = $api->repositories($searchTerm, 'updated', 'desc');
		$repos = [];
		foreach($data['items'] as $repo){
			$repo['description'] = QMStr::truncate($repo['description'], 65534);
            foreach ($repo as $key => $value) {
                if (!static::hasColumn($key)) {
                    QMLog::error("Repo table does not have column: `$key` to store value: ".\App\Logging\QMLog::print_r($value, true));
                    unset($repo[$key]);
                }
            }
            $repo[GithubRepository::FIELD_GITHUB_REPOSITORY_ID] = $repo[GithubRepository::FIELD_ID];
            $name = $repo['name'];
			unset($repo['has_discussions']);
            try {
                $repos[$name] = GithubRepository::updateOrCreate([GithubRepository::FIELD_ID =>
                    $repo[GithubRepository::FIELD_ID]], $repo);
            } catch (\Throwable $e) {
                QMLog::error("Could not save repo '$name' because ".$e->getMessage(),
                    ['exception' => $e]);
            }
		}
		return (new static)->newCollection($repos);
		//return parent::search($query, $callback);
	}
	public function getFillable(): array{
		$arr = parent::getFillable(); 
		$arr[] = self::FIELD_ID;
		return $this->fillable = array_unique($arr);
	}
	public function getUserVariable(int $userId): UserVariable{
		$variable = $this->getVariable($userId);
		$uv = $variable->getOrCreateUserVariable($userId);
		$name = $uv->getVariableName();
		if(!isset($this->appends['user_variables'][$name])){
			$this->appends['user_variables'][$name] = $uv;
			$tag = new UserTag;
			$tag->setTaggedUserVariable($uv);
			$main = CodeCommitsCommonVariable::instance()->getUserVariable($userId);
			$tag->setTagUserVariable($main);
			$tag->conversion_factor = 1;
			try {
				$tag->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
		return $uv;
	}
	public function getImage(): string{
		$owner = $this->owner;
		return $owner->avatar_url;
	}
	public function getVariable(int $userId): Variable{
		$name = $this->getVariableName();
		if($v = Variable::findByName($name)){
			return $v;
		}
		$commitsV = Variable::findInMemoryOrDB(CodeCommitsCommonVariable::ID);
		/** @var Variable $newV */
		$newV = $commitsV->clone([
			                         Variable::FIELD_NAME => $name,
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
		return $newV;
	}
	protected function getVariableName(): ?string{
		return "Github Code Commits to $this->full_name";
	}
}
