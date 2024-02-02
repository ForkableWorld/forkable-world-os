<?php

namespace Tests\Feature\API\GithubRepositories;

use App\Models\GithubRepositories;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

abstract class BaseGithubRepositoriesTest extends TestCase
{

    protected $fields = [

        'id',

        'client_id',

        'created_at',

        'deleted_at',

        'updated_at',

        'user_id',

        'github_repository_id',

        'node_id',

        'name',

        'full_name',

        'private',

        'owner',

        'html_url',

        'description',

        'fork',

        'url',

        'forks_url',

        'keys_url',

        'collaborators_url',

        'teams_url',

        'hooks_url',

        'issue_events_url',

        'events_url',

        'assignees_url',

        'branches_url',

        'tags_url',

        'blobs_url',

        'git_tags_url',

        'git_refs_url',

        'trees_url',

        'statuses_url',

        'languages_url',

        'stargazers_url',

        'contributors_url',

        'subscribers_url',

        'subscription_url',

        'commits_url',

        'git_commits_url',

        'comments_url',

        'issue_comment_url',

        'contents_url',

        'compare_url',

        'merges_url',

        'archive_url',

        'downloads_url',

        'issues_url',

        'pulls_url',

        'milestones_url',

        'notifications_url',

        'labels_url',

        'releases_url',

        'deployments_url',

        'pushed_at',

        'git_url',

        'ssh_url',

        'clone_url',

        'svn_url',

        'homepage',

        'size',

        'stargazers_count',

        'watchers_count',

        'language',

        'has_issues',

        'has_projects',

        'has_downloads',

        'has_wiki',

        'has_pages',

        'forks_count',

        'archived',

        'disabled',

        'open_issues_count',

        'allow_forking',

        'is_template',

        'topics',

        'visibility',

        'forks',

        'open_issues',

        'watchers',

        'default_branch',

        'permissions',

        'temp_clone_token',

        'allow_squash_merge',

        'allow_merge_commit',

        'allow_rebase_merge',

        'allow_auto_merge',

        'delete_branch_on_merge',

        'network_count',

        'subscribers_count',

    ];

    protected function getURI(string $path = '')
    {
        $uri = "/github_repositories";
        if ($path) {
            $uri = $uri . '/' . $path;
        }
        return $uri;
    }

    /**
     * @param array $attributes
     * @return GithubRepositories
     */
    protected function make(array $attributes = [])
    {
        $item = GithubRepositories::factory()->make($attributes);
        return $item;
    }

    /**
     * @param array $attributes
     * @return GithubRepositories
     */
    protected function makeSave(array $attributes = [])
    {
        $item = $this->make($attributes);
        $item->save();
        return $item;
    }

}
