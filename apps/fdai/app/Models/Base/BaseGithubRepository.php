<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\BaseModel;
use App\Models\OAClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class BaseGithubRepository
 *
 * @property int $id
 * @property string $client_id
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property Carbon $updated_at
 * @property int $user_id
 * @property int $github_repository_id
 * @property string $node_id
 * @property string $name
 * @property string $full_name
 * @property bool $private
 * @property string $owner
 * @property string $html_url
 * @property string $description
 * @property bool $fork
 * @property string $url
 * @property string $forks_url
 * @property string $keys_url
 * @property string $collaborators_url
 * @property string $teams_url
 * @property string $hooks_url
 * @property string $issue_events_url
 * @property string $events_url
 * @property string $assignees_url
 * @property string $branches_url
 * @property string $tags_url
 * @property string $blobs_url
 * @property string $git_tags_url
 * @property string $git_refs_url
 * @property string $trees_url
 * @property string $statuses_url
 * @property string $languages_url
 * @property string $stargazers_url
 * @property string $contributors_url
 * @property string $subscribers_url
 * @property string $subscription_url
 * @property string $commits_url
 * @property string $git_commits_url
 * @property string $comments_url
 * @property string $issue_comment_url
 * @property string $contents_url
 * @property string $compare_url
 * @property string $merges_url
 * @property string $archive_url
 * @property string $downloads_url
 * @property string $issues_url
 * @property string $pulls_url
 * @property string $milestones_url
 * @property string $notifications_url
 * @property string $labels_url
 * @property string $releases_url
 * @property string $deployments_url
 * @property string $pushed_at
 * @property string $git_url
 * @property string $ssh_url
 * @property string $clone_url
 * @property string $svn_url
 * @property string $homepage
 * @property int $size
 * @property int $stargazers_count
 * @property int $watchers_count
 * @property string $language
 * @property bool $has_issues
 * @property bool $has_projects
 * @property bool $has_downloads
 * @property bool $has_wiki
 * @property bool $has_pages
 * @property int $forks_count
 * @property bool $archived
 * @property bool $disabled
 * @property int $open_issues_count
 * @property bool $allow_forking
 * @property bool $is_template
 * @property string $topics
 * @property string $visibility
 * @property int $forks
 * @property int $open_issues
 * @property int $watchers
 * @property string $default_branch
 * @property string $permissions
 * @property string $temp_clone_token
 * @property bool $allow_squash_merge
 * @property bool $allow_merge_commit
 * @property bool $allow_rebase_merge
 * @property bool $allow_auto_merge
 * @property bool $delete_branch_on_merge
 * @property int $network_count
 * @property int $subscribers_count
 * @property OAClient $oa_client
 * @property \App\Models\User $wp_user
 * @package App\Models\Base
 * @property string|null $mirror_url
 * @property array|null $license
 * @property int|null $web_commit_signoff_required
 * @property-read OAClient|null $client
 * @property mixed|null $calculated
 * @property-read array $invalid_record_for
 * @property mixed|null $raw
 * @property-read string $report_title
 * @property-read array|mixed|string|string[]|null $rule_for
 * @property-read array $rules_for
 * @property-read string $subtitle
 * @property-read string $title
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, int $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereAllowAutoMerge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereAllowForking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereAllowMergeCommit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereAllowRebaseMerge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereAllowSquashMerge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereArchiveUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereArchived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereAssigneesUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereBlobsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereBranchesUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereCloneUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereCollaboratorsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereCommentsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereCommitsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereCompareUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereContentsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereContributorsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereDefaultBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereDeleteBranchOnMerge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereDeploymentsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereDisabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereDownloadsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereEventsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereFork($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereForks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereForksCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereForksUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereGitCommitsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereGitRefsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereGitTagsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereGitUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereGithubRepositoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereHasDownloads($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereHasIssues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereHasPages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereHasProjects($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereHasWiki($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereHomepage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereHooksUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereHtmlUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereIsTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereIssueCommentUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereIssueEventsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereIssuesUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereKeysUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereLabelsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereLanguagesUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereLicense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereMergesUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereMilestonesUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereMirrorUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereNetworkCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereNodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereNotificationsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereOpenIssues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereOpenIssuesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository wherePrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository wherePullsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository wherePushedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereReleasesUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereSshUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereStargazersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereStargazersUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereStatusesUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereSubscribersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereSubscribersUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereSubscriptionUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereSvnUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereTagsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereTeamsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereTempCloneToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereTopics($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereTreesUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereWatchers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereWatchersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository whereWebCommitSignoffRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseGithubRepository withoutTrashed()
 * @mixin \Eloquent
 */
class BaseGithubRepository extends BaseModel
{
	use SoftDeletes;
	public const FIELD_ALLOW_AUTO_MERGE = 'allow_auto_merge';
	public const FIELD_ALLOW_FORKING = 'allow_forking';
	public const FIELD_ALLOW_MERGE_COMMIT = 'allow_merge_commit';
	public const FIELD_ALLOW_REBASE_MERGE = 'allow_rebase_merge';
	public const FIELD_ALLOW_SQUASH_MERGE = 'allow_squash_merge';
	public const FIELD_ARCHIVE_URL = 'archive_url';
	public const FIELD_ARCHIVED = 'archived';
	public const FIELD_ASSIGNEES_URL = 'assignees_url';
	public const FIELD_BLOBS_URL = 'blobs_url';
	public const FIELD_BRANCHES_URL = 'branches_url';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CLONE_URL = 'clone_url';
	public const FIELD_COLLABORATORS_URL = 'collaborators_url';
	public const FIELD_COMMENTS_URL = 'comments_url';
	public const FIELD_COMMITS_URL = 'commits_url';
    public const FIELD_MIRROR_URL = 'mirror_url';
    public const FIELD_LICENSE = 'license';
    public const FIELD_WEB_COMMIT_SIGNOFF_REQUIRED = 'web_commit_signoff_required';
	public const FIELD_COMPARE_URL = 'compare_url';
	public const FIELD_CONTENTS_URL = 'contents_url';
	public const FIELD_CONTRIBUTORS_URL = 'contributors_url';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DEFAULT_BRANCH = 'default_branch';
	public const FIELD_DELETE_BRANCH_ON_MERGE = 'delete_branch_on_merge';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DEPLOYMENTS_URL = 'deployments_url';
	public const FIELD_DESCRIPTION = 'description';
	public const FIELD_DISABLED = 'disabled';
	public const FIELD_DOWNLOADS_URL = 'downloads_url';
	public const FIELD_EVENTS_URL = 'events_url';
	public const FIELD_FORK = 'fork';
	public const FIELD_FORKS = 'forks';
	public const FIELD_FORKS_COUNT = 'forks_count';
	public const FIELD_FORKS_URL = 'forks_url';
	public const FIELD_FULL_NAME = 'full_name';
	public const FIELD_GIT_COMMITS_URL = 'git_commits_url';
	public const FIELD_GIT_REFS_URL = 'git_refs_url';
	public const FIELD_GIT_TAGS_URL = 'git_tags_url';
	public const FIELD_GIT_URL = 'git_url';
	public const FIELD_GITHUB_REPOSITORY_ID = 'github_repository_id';
	public const FIELD_HAS_DOWNLOADS = 'has_downloads';
	public const FIELD_HAS_ISSUES = 'has_issues';
	public const FIELD_HAS_PAGES = 'has_pages';
	public const FIELD_HAS_PROJECTS = 'has_projects';
	public const FIELD_HAS_WIKI = 'has_wiki';
	public const FIELD_HOMEPAGE = 'homepage';
	public const FIELD_HOOKS_URL = 'hooks_url';
	public const FIELD_HTML_URL = 'html_url';
	public const FIELD_ID = 'id';
	public const FIELD_IS_TEMPLATE = 'is_template';
	public const FIELD_ISSUE_COMMENT_URL = 'issue_comment_url';
	public const FIELD_ISSUE_EVENTS_URL = 'issue_events_url';
	public const FIELD_ISSUES_URL = 'issues_url';
	public const FIELD_KEYS_URL = 'keys_url';
	public const FIELD_LABELS_URL = 'labels_url';
	public const FIELD_LANGUAGE = 'language';
	public const FIELD_LANGUAGES_URL = 'languages_url';
	public const FIELD_MERGES_URL = 'merges_url';
	public const FIELD_MILESTONES_URL = 'milestones_url';
	public const FIELD_NAME = 'name';
	public const FIELD_NETWORK_COUNT = 'network_count';
	public const FIELD_NODE_ID = 'node_id';
	public const FIELD_NOTIFICATIONS_URL = 'notifications_url';
	public const FIELD_OPEN_ISSUES = 'open_issues';
	public const FIELD_OPEN_ISSUES_COUNT = 'open_issues_count';
	public const FIELD_OWNER = 'owner';
	public const FIELD_PERMISSIONS = 'permissions';
	public const FIELD_PRIVATE = 'private';
	public const FIELD_PULLS_URL = 'pulls_url';
	public const FIELD_PUSHED_AT = 'pushed_at';
	public const FIELD_RELEASES_URL = 'releases_url';
    public const FIELD_SCORE = 'score';
	public const FIELD_SIZE = 'size';
	public const FIELD_SSH_URL = 'ssh_url';
	public const FIELD_STARGAZERS_COUNT = 'stargazers_count';
	public const FIELD_STARGAZERS_URL = 'stargazers_url';
	public const FIELD_STATUSES_URL = 'statuses_url';
	public const FIELD_SUBSCRIBERS_COUNT = 'subscribers_count';
	public const FIELD_SUBSCRIBERS_URL = 'subscribers_url';
	public const FIELD_SUBSCRIPTION_URL = 'subscription_url';
	public const FIELD_SVN_URL = 'svn_url';
	public const FIELD_TAGS_URL = 'tags_url';
	public const FIELD_TEAMS_URL = 'teams_url';
	public const FIELD_TEMP_CLONE_TOKEN = 'temp_clone_token';
	public const FIELD_TOPICS = 'topics';
	public const FIELD_TREES_URL = 'trees_url';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_URL = 'url';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_VISIBILITY = 'visibility';
	public const FIELD_WATCHERS = 'watchers';
	public const FIELD_WATCHERS_COUNT = 'watchers_count';
	public const TABLE = 'github_repositories';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	public $incrementing = false;

	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
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
        self::FIELD_LICENSE => 'array',
		self::FIELD_MERGES_URL => 'string',
		self::FIELD_MILESTONES_URL => 'string',
		self::FIELD_NAME => 'string',
		self::FIELD_NETWORK_COUNT => 'int',
		self::FIELD_NODE_ID => 'string',
		self::FIELD_NOTIFICATIONS_URL => 'string',
		self::FIELD_OPEN_ISSUES => 'int',
		self::FIELD_OPEN_ISSUES_COUNT => 'int',
		self::FIELD_OWNER => 'string',
		self::FIELD_PERMISSIONS => 'string',
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
		self::FIELD_TOPICS => 'string',
		self::FIELD_TREES_URL => 'string',
		self::FIELD_URL => 'string',
		self::FIELD_USER_ID => 'int',
		self::FIELD_VISIBILITY => 'string',
		self::FIELD_WATCHERS => 'int',
		self::FIELD_WATCHERS_COUNT => 'int'
	];

	protected array $rules = [
		self::FIELD_ALLOW_AUTO_MERGE => 'required|boolean',
		self::FIELD_ALLOW_FORKING => 'required|boolean',
		self::FIELD_ALLOW_MERGE_COMMIT => 'required|boolean',
		self::FIELD_ALLOW_REBASE_MERGE => 'required|boolean',
		self::FIELD_ALLOW_SQUASH_MERGE => 'required|boolean',
		self::FIELD_ARCHIVED => 'required|boolean',
		self::FIELD_ARCHIVE_URL => 'required|max:255',
		self::FIELD_ASSIGNEES_URL => 'required|max:255',
		self::FIELD_BLOBS_URL => 'required|max:255',
		self::FIELD_BRANCHES_URL => 'required|max:255',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_CLONE_URL => 'required|max:255',
		self::FIELD_COLLABORATORS_URL => 'required|max:255',
		self::FIELD_COMMENTS_URL => 'required|max:255',
		self::FIELD_COMMITS_URL => 'required|max:255',
		self::FIELD_COMPARE_URL => 'required|max:255',
		self::FIELD_CONTENTS_URL => 'required|max:255',
		self::FIELD_CONTRIBUTORS_URL => 'required|max:255',
		self::FIELD_DEFAULT_BRANCH => 'required|max:255',
		self::FIELD_DELETE_BRANCH_ON_MERGE => 'required|boolean',
		self::FIELD_DEPLOYMENTS_URL => 'required|max:255',
		self::FIELD_DESCRIPTION => 'required|max:255',
		self::FIELD_DISABLED => 'required|boolean',
		self::FIELD_DOWNLOADS_URL => 'required|max:255',
		self::FIELD_EVENTS_URL => 'required|max:255',
		self::FIELD_FORK => 'required|boolean',
		self::FIELD_FORKS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_FORKS_COUNT => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_FORKS_URL => 'required|max:255',
		self::FIELD_FULL_NAME => 'required|max:255',
		self::FIELD_GITHUB_REPOSITORY_ID => 'required|integer|min:-2147483648|max:2147483647|unique:github_repositories,github_repository_id',
		self::FIELD_GIT_COMMITS_URL => 'required|max:255',
		self::FIELD_GIT_REFS_URL => 'required|max:255',
		self::FIELD_GIT_TAGS_URL => 'required|max:255',
		self::FIELD_GIT_URL => 'required|max:255|unique:github_repositories,git_url',
		self::FIELD_HAS_DOWNLOADS => 'required|boolean',
		self::FIELD_HAS_ISSUES => 'required|boolean',
		self::FIELD_HAS_PAGES => 'required|boolean',
		self::FIELD_HAS_PROJECTS => 'required|boolean',
		self::FIELD_HAS_WIKI => 'required|boolean',
		self::FIELD_HOMEPAGE => 'required|max:255',
		self::FIELD_HOOKS_URL => 'required|max:255',
		self::FIELD_HTML_URL => 'required|max:255|unique:github_repositories,html_url',
		self::FIELD_ISSUES_URL => 'required|max:255',
		self::FIELD_ISSUE_COMMENT_URL => 'required|max:255',
		self::FIELD_ISSUE_EVENTS_URL => 'required|max:255',
		self::FIELD_IS_TEMPLATE => 'required|boolean',
		self::FIELD_KEYS_URL => 'required|max:255',
		self::FIELD_LABELS_URL => 'required|max:255',
		self::FIELD_LANGUAGE => 'required|max:255',
		self::FIELD_LANGUAGES_URL => 'required|max:255',
		self::FIELD_MERGES_URL => 'required|max:255',
		self::FIELD_MILESTONES_URL => 'required|max:255',
		self::FIELD_NAME => 'required|max:255|unique:github_repositories,name',
		self::FIELD_NETWORK_COUNT => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_NODE_ID => 'required|max:255',
		self::FIELD_NOTIFICATIONS_URL => 'required|max:255',
		self::FIELD_OPEN_ISSUES => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_OPEN_ISSUES_COUNT => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_OWNER => 'required',
		self::FIELD_PERMISSIONS => 'required',
		self::FIELD_PRIVATE => 'required|boolean',
		self::FIELD_PULLS_URL => 'required|max:255',
		self::FIELD_PUSHED_AT => 'required|max:255',
		self::FIELD_RELEASES_URL => 'required|max:255',
		self::FIELD_SIZE => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_SSH_URL => 'required|max:255|unique:github_repositories,ssh_url',
		self::FIELD_STARGAZERS_COUNT => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_STARGAZERS_URL => 'required|max:255',
		self::FIELD_STATUSES_URL => 'required|max:255',
		self::FIELD_SUBSCRIBERS_COUNT => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_SUBSCRIBERS_URL => 'required|max:255',
		self::FIELD_SUBSCRIPTION_URL => 'required|max:255',
		self::FIELD_SVN_URL => 'required|max:255',
		self::FIELD_TAGS_URL => 'required|max:255',
		self::FIELD_TEAMS_URL => 'required|max:255',
		self::FIELD_TEMP_CLONE_TOKEN => 'required|max:255',
		self::FIELD_TOPICS => 'required',
		self::FIELD_TREES_URL => 'required|max:255',
		self::FIELD_URL => 'required|max:255|unique:github_repositories,url',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_VISIBILITY => 'required|max:255',
		self::FIELD_WATCHERS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_WATCHERS_COUNT => 'required|integer|min:-2147483648|max:2147483647'
	];
	protected $hints = [
		self::FIELD_ID => 'Automatically generated unique id for the github repository',
		self::FIELD_CLIENT_ID => 'The ID for the API client that created the record',
		self::FIELD_CREATED_AT => 'The time the record was originally created',
		self::FIELD_DELETED_AT => 'The time the record was deleted',
		self::FIELD_UPDATED_AT => 'The time the record was last modified',
		self::FIELD_USER_ID => 'The user ID for the owner of the record',
		self::FIELD_GITHUB_REPOSITORY_ID => 'Github repository id Example: 158861117',
		self::FIELD_NODE_ID => 'Example: MDEwOlJlcG9zaXRvcnkxNTg4NjExMTc=',
		self::FIELD_NAME => 'Example: qm-api',
		self::FIELD_FULL_NAME => 'Example: mikepsinn/qm-api',
		self::FIELD_PRIVATE => 'Example: 1',
		self::FIELD_OWNER => 'Example: {login:mikepsinn,id:2808553,node_id:MDQ6VXNlcjI4MDg1NTM=,avatar_url:https://avatars.githubusercontent.com/u/2808553?v=4,gravatar_id:,url:https://api.github.com/users/mikepsinn,html_url:https://github.com/mikepsinn,followers_url:https://api.github.com/users/mikepsinn/followers,following_url:https://api.github.com/users/mikepsinn/following{/other_user},gists_url:https://api.github.com/users/mikepsinn/gists{/gist_id},starred_url:https://api.github.com/users/mikepsinn/starred{/owner}{/repo},subscriptions_url:https://api.github.com/users/mikepsinn/subscriptions,organizations_url:https://api.github.com/users/mikepsinn/orgs,repos_url:https://api.github.com/users/mikepsinn/repos,events_url:https://api.github.com/users/mikepsinn/events{/privacy},received_events_url:https://api.github.com/users/mikepsinn/received_events,type:User,site_admin:false}',
		self::FIELD_HTML_URL => 'Example: https://github.com/mikepsinn/qm-api',
		self::FIELD_DESCRIPTION => 'Example: I\'m a description',
		self::FIELD_FORK => 'Example: ',
		self::FIELD_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api',
		self::FIELD_FORKS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/forks',
		self::FIELD_KEYS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/keys{/key_id}',
		self::FIELD_COLLABORATORS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/collaborators{/collaborator}',
		self::FIELD_TEAMS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/teams',
		self::FIELD_HOOKS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/hooks',
		self::FIELD_ISSUE_EVENTS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/issues/events{/number}',
		self::FIELD_EVENTS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/events',
		self::FIELD_ASSIGNEES_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/assignees{/user}',
		self::FIELD_BRANCHES_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/branches{/branch}',
		self::FIELD_TAGS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/tags',
		self::FIELD_BLOBS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/git/blobs{/sha}',
		self::FIELD_GIT_TAGS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/git/tags{/sha}',
		self::FIELD_GIT_REFS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/git/refs{/sha}',
		self::FIELD_TREES_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/git/trees{/sha}',
		self::FIELD_STATUSES_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/statuses/{sha}',
		self::FIELD_LANGUAGES_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/languages',
		self::FIELD_STARGAZERS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/stargazers',
		self::FIELD_CONTRIBUTORS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/contributors',
		self::FIELD_SUBSCRIBERS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/subscribers',
		self::FIELD_SUBSCRIPTION_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/subscription',
		self::FIELD_COMMITS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/commits{/sha}',
		self::FIELD_GIT_COMMITS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/git/commits{/sha}',
		self::FIELD_COMMENTS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/comments{/number}',
		self::FIELD_ISSUE_COMMENT_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/issues/comments{/number}',
		self::FIELD_CONTENTS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/contents/{+path}',
		self::FIELD_COMPARE_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/compare/{base}...{head}',
		self::FIELD_MERGES_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/merges',
		self::FIELD_ARCHIVE_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/{archive_format}{/ref}',
		self::FIELD_DOWNLOADS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/downloads',
		self::FIELD_ISSUES_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/issues{/number}',
		self::FIELD_PULLS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/pulls{/number}',
		self::FIELD_MILESTONES_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/milestones{/number}',
		self::FIELD_NOTIFICATIONS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/notifications{?since,all,participating}',
		self::FIELD_LABELS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/labels{/name}',
		self::FIELD_RELEASES_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/releases{/id}',
		self::FIELD_DEPLOYMENTS_URL => 'Example: https://api.github.com/repos/mikepsinn/qm-api/deployments',
		self::FIELD_PUSHED_AT => 'Example: 2021-10-24T00:07:07Z',
		self::FIELD_GIT_URL => 'Example: git://github.com/mikepsinn/qm-api.git',
		self::FIELD_SSH_URL => 'Example: git@github.com:mikepsinn/qm-api.git',
		self::FIELD_CLONE_URL => 'Example: https://github.com/mikepsinn/qm-api.git',
		self::FIELD_SVN_URL => 'Example: https://github.com/mikepsinn/qm-api',
		self::FIELD_HOMEPAGE => 'Example: https://quantimo.do',
		self::FIELD_SIZE => 'Example: 178451',
		self::FIELD_STARGAZERS_COUNT => 'Example: 2',
		self::FIELD_WATCHERS_COUNT => 'Example: 2',
		self::FIELD_LANGUAGE => 'Example: PHP',
		self::FIELD_HAS_ISSUES => 'Example: 1',
		self::FIELD_HAS_PROJECTS => 'Example: 1',
		self::FIELD_HAS_DOWNLOADS => 'Example: 1',
		self::FIELD_HAS_WIKI => 'Example: 1',
		self::FIELD_HAS_PAGES => 'Example: ',
		self::FIELD_FORKS_COUNT => 'Example: 0',
		self::FIELD_ARCHIVED => 'Example: ',
		self::FIELD_DISABLED => 'Example: ',
		self::FIELD_OPEN_ISSUES_COUNT => 'Example: 89',
		self::FIELD_ALLOW_FORKING => 'Example: 1',
		self::FIELD_IS_TEMPLATE => 'Example: ',
		self::FIELD_TOPICS => 'Example: [digital-health,health,healthcare-data]',
		self::FIELD_VISIBILITY => 'Example: private',
		self::FIELD_FORKS => 'Example: 0',
		self::FIELD_OPEN_ISSUES => 'Example: 89',
		self::FIELD_WATCHERS => 'Example: 2',
		self::FIELD_DEFAULT_BRANCH => 'Example: develop',
		self::FIELD_PERMISSIONS => 'Example: {admin:true,maintain:true,push:true,triage:true,pull:true}',
		self::FIELD_TEMP_CLONE_TOKEN => 'Example: AAVNV2SECRETYTBOS7ZI',
		self::FIELD_ALLOW_SQUASH_MERGE => 'Example: 1',
		self::FIELD_ALLOW_MERGE_COMMIT => 'Example: 1',
		self::FIELD_ALLOW_REBASE_MERGE => 'Example: 1',
		self::FIELD_ALLOW_AUTO_MERGE => 'Example: ',
		self::FIELD_DELETE_BRANCH_ON_MERGE => 'Example: ',
		self::FIELD_NETWORK_COUNT => 'Example: 0',
		self::FIELD_SUBSCRIBERS_COUNT => 'Example: 0'
	];

	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => \App\Models\GithubRepository::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => \App\Models\GithubRepository::FIELD_CLIENT_ID,
			'methodName' => 'oa_client'
		],
		'wp_user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => \App\Models\GithubRepository::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => \App\Models\GithubRepository::FIELD_USER_ID,
			'methodName' => 'wp_user'
		]
	];

	public function oa_client(): BelongsTo
	{
		return $this->belongsTo(OAClient::class, \App\Models\GithubRepository::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID, \App\Models\GithubRepository::FIELD_CLIENT_ID);
	}

	public function wp_user(): BelongsTo
	{
		return $this->belongsTo(\App\Models\User::class, \App\Models\GithubRepository::FIELD_USER_ID, \App\Models\User::FIELD_ID, \App\Models\GithubRepository::FIELD_USER_ID);
	}
}
