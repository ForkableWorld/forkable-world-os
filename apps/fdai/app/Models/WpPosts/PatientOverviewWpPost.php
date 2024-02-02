<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models\WpPosts;
use AdvancedCustomFields;
use App\Exceptions\DeletedUserException;
use App\Models\GlobalVariableRelationship;
use App\Models\Application;
use App\Models\BaseModel;
use App\Models\Connection;
use App\Models\Connector;
use App\Models\UserVariableRelationship;
use App\Models\OAClient;
use App\Models\SentEmail;
use App\Models\SpreadsheetImporter;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\WpPost;
use App\Models\WpPostmetum;
use App\Models\WpTerm;
use App\Models\WpTermRelationship;
use App\Slim\Model\User\QMUser;
use Corcel\Model\Comment;
use Corcel\Model\Meta\ThumbnailMeta;
use Corcel\Model\Post;
use Corcel\Model\Taxonomy;
use Corcel\Model\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
/**
 * App\Models\WpPosts\PatientOverviewWpPost
 * @property int $ID
 * @property int $post_author
 * @property Carbon|null $post_date
 * @property Carbon|null $post_date_gmt
 * @property string $post_content
 * @property string $post_title
 * @property string $post_excerpt
 * @property string $post_status
 * @property string $comment_status
 * @property string $ping_status
 * @property string $post_password
 * @property string $post_name
 * @property string $to_ping
 * @property string $pinged
 * @property Carbon|null $post_modified
 * @property Carbon|null $post_modified_gmt
 * @property string $post_content_filtered
 * @property int $post_parent
 * @property string $guid
 * @property int $menu_order
 * @property string $post_type
 * @property string $post_mime_type
 * @property int $comment_count
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @property-read \App\Models\User $user
 * @property-read int|null $wp_comments_count
 * @property-read Collection|WpPostmetum[] $wp_postmeta
 * @property-read int|null $wp_postmeta_count
 * @method static Builder|PatientOverviewWpPost newModelQuery()
 * @method static Builder|PatientOverviewWpPost newQuery()
 * @method static Builder|PatientOverviewWpPost query()
 * @method static Builder|PatientOverviewWpPost whereClientId($value)
 * @method static Builder|PatientOverviewWpPost whereCommentCount($value)
 * @method static Builder|PatientOverviewWpPost whereCommentStatus($value)
 * @method static Builder|PatientOverviewWpPost whereCreatedAt($value)
 * @method static Builder|PatientOverviewWpPost whereDeletedAt($value)
 * @method static Builder|PatientOverviewWpPost whereGuid($value)
 * @method static Builder|PatientOverviewWpPost whereID($value)
 * @method static Builder|PatientOverviewWpPost whereMenuOrder($value)
 * @method static Builder|PatientOverviewWpPost wherePingStatus($value)
 * @method static Builder|PatientOverviewWpPost wherePinged($value)
 * @method static Builder|PatientOverviewWpPost wherePostAuthor($value)
 * @method static Builder|PatientOverviewWpPost wherePostContent($value)
 * @method static Builder|PatientOverviewWpPost wherePostContentFiltered($value)
 * @method static Builder|PatientOverviewWpPost wherePostDate($value)
 * @method static Builder|PatientOverviewWpPost wherePostDateGmt($value)
 * @method static Builder|PatientOverviewWpPost wherePostExcerpt($value)
 * @method static Builder|PatientOverviewWpPost wherePostMimeType($value)
 * @method static Builder|PatientOverviewWpPost wherePostModified($value)
 * @method static Builder|PatientOverviewWpPost wherePostModifiedGmt($value)
 * @method static Builder|PatientOverviewWpPost wherePostName($value)
 * @method static Builder|PatientOverviewWpPost wherePostParent($value)
 * @method static Builder|PatientOverviewWpPost wherePostPassword($value)
 * @method static Builder|PatientOverviewWpPost wherePostStatus($value)
 * @method static Builder|PatientOverviewWpPost wherePostTitle($value)
 * @method static Builder|PatientOverviewWpPost wherePostType($value)
 * @method static Builder|PatientOverviewWpPost whereToPing($value)
 * @method static Builder|PatientOverviewWpPost whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|GlobalVariableRelationship[] $global_variable_relationships
 * @property-read int|null $global_variable_relationships_count
 * @property-read Collection|Application[] $applications
 * @property-read int|null $applications_count
 * @property-read Collection|Connection[] $connections
 * @property-read int|null $connections_count
 * @property-read Collection|Connector[] $connectors
 * @property-read int|null $connectors_count
 * @property-read Collection|UserVariableRelationship[] $user_variable_relationships
 * @property-read int|null $correlations_count
 * @property-read Collection|SentEmail[] $sent_emails
 * @property-read int|null $sent_emails_count
 * @property-read Collection|UserVariable[] $user_variables
 * @property-read int|null $user_variables_count
 * @property-read Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @property-read Collection|VariableCategory[] $variable_categories
 * @property-read int|null $variable_categories_count
 * @property-read Collection|Variable[] $variables
 * @property-read int|null $variables_count
 * @property-read Collection|WpTermRelationship[] $wp_term_relationships
 * @property-read int|null $wp_term_relationships_count
 * @property-read Collection|Post[] $attachment
 * @property-read int|null $attachment_count
 * @property-read User|null $author
 * @property-read Collection|Post[] $children
 * @property-read int|null $children_count
 * @property-read Collection|Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read Collection|WpPostmetum[] $fields
 * @property-read int|null $fields_count
 * @property-read AdvancedCustomFields $acf
 * @property-read string $content
 * @property-read string $excerpt
 * @property-read string $image
 * @property-read array $keywords
 * @property-read string $keywords_str
 * @property-read string $main_category_slug
 * @property-read array $terms
 * @property-read Collection|WpPostmetum[] $meta
 * @property-read int|null $meta_count
 * @property-read Post|null $parent
 * @property-read Collection|Post[] $revision
 * @property-read int|null $revision_count
 * @property-read Collection|Taxonomy[] $taxonomies
 * @property-read int|null $taxonomies_count
 * @property-read ThumbnailMeta $thumbnail
 * @method static Builder|WpPost hasMeta($meta, $value = null, $operator = '=')
 * @method static Builder|WpPost hasMetaLike($meta, $value = null)
 * @method static Builder|WpPost newest()
 * @method static Builder|WpPost oldest()
 * @property-read WpTerm $category_term
 * @property-read string $main_category_name
 * @property-read WpTerm $parent_category_term
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read int|null $wp_comments_where_comment_post__i_d_count
 * @property int|null $record_size_in_kb
 * @property-read Collection|SpreadsheetImporter[] $spreadsheet_importers
 * @property-read int|null $spreadsheet_importers_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\WpPosts\PatientOverviewWpPost
 *     whereRecordSizeInKb($value)
 * @property mixed $raw
 * @property-read OAClient|null $client
 * @property-read OAClient|null $oa_client
 */
class PatientOverviewWpPost extends WpPost {
	/**
	 * @return QMUser
	 */
	public function getPatient(): QMUser{
		return $this->getQMUser();
	}
	/**
	 * @return string
	 */
	public function generatePostContent(): string{
		$patient = $this->getPatient();
		$html = '';
		//$html .= $patient->getWpPostPreviewTableHtml($this->post_excerpt, 1000); This is way too huge for database
		//$html .= $patient->getPostArchiveButton();
		$html .= $patient->getDataQuantityListRoundedButtonsHTML();
		//$a = $qmUser->getRootCauseAnalysis();
		//$str .= $a->getWpButtonHTML();
		if($includeFileButtons = false){ // This is really slow!
			$buttons = $patient->getFileButtons(".pdf");
			foreach($buttons as $button){
				$html .= $button->getRectangleWPButton();
			}
		}
		return $this->post_content = "
            <div style='max-width: 980px;'>
                $html
            </div>
        ";
	}
	/**
	 * @param int $userId
	 * @return PatientOverviewWpPost
	 * @throws DeletedUserException
	 */
	public static function newByUserId(int $userId): PatientOverviewWpPost{
		$p = new self();
		$u = QMUser::find($userId);
		$p->post_excerpt = "Studies and data for $u->displayName";
		$p->post_author = $userId;
		$p->generatePostContent();
		$p->post_title = "Overview for " . $p->getUser()->display_name;
		return $p;
	}
}
