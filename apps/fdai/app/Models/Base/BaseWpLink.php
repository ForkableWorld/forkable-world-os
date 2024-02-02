<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/** Created by Reliese Model.
 */
namespace App\Models\Base;
use App\Models\BaseModel;
use App\Models\WpLink;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseWpLink
 * @property int $link_id
 * @property string $link_url
 * @property string $link_name
 * @property string $link_image
 * @property string $link_target
 * @property string $link_description
 * @property string $link_visible
 * @property int $link_owner
 * @property int $link_rating
 * @property Carbon $link_updated
 * @property string $link_rel
 * @property string $link_notes
 * @property string $link_rss
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property string $client_id
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpLink onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkRel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkRss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereLinkVisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpLink withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpLink withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseWpLink extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_LINK_DESCRIPTION = 'link_description';
	public const FIELD_LINK_ID = 'link_id';
	public const FIELD_LINK_IMAGE = 'link_image';
	public const FIELD_LINK_NAME = 'link_name';
	public const FIELD_LINK_NOTES = 'link_notes';
	public const FIELD_LINK_OWNER = 'link_owner';
	public const FIELD_LINK_RATING = 'link_rating';
	public const FIELD_LINK_REL = 'link_rel';
	public const FIELD_LINK_RSS = 'link_rss';
	public const FIELD_LINK_TARGET = 'link_target';
	public const FIELD_LINK_UPDATED = 'link_updated';
	public const FIELD_LINK_URL = 'link_url';
	public const FIELD_LINK_VISIBLE = 'link_visible';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'wp_links';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'During the rise of popularity of blogging having a blogroll (links to other sites) on your site was very much in fashion. This table holds all those links for you.';
	protected $primaryKey = 'link_id';
	protected $casts = [
        self::FIELD_LINK_UPDATED => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_LINK_DESCRIPTION => 'string',
		self::FIELD_LINK_ID => 'int',
		self::FIELD_LINK_IMAGE => 'string',
		self::FIELD_LINK_NAME => 'string',
		self::FIELD_LINK_NOTES => 'string',
		self::FIELD_LINK_OWNER => 'int',
		self::FIELD_LINK_RATING => 'int',
		self::FIELD_LINK_REL => 'string',
		self::FIELD_LINK_RSS => 'string',
		self::FIELD_LINK_TARGET => 'string',
		self::FIELD_LINK_URL => 'string',
		self::FIELD_LINK_VISIBLE => 'string',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_LINK_DESCRIPTION => 'nullable|max:255',
		self::FIELD_LINK_ID => 'required|numeric|min:0|unique:wp_links,link_id',
		self::FIELD_LINK_IMAGE => 'nullable|max:255',
		self::FIELD_LINK_NAME => 'nullable|max:255',
		self::FIELD_LINK_NOTES => 'nullable|max:16777215',
		self::FIELD_LINK_OWNER => 'nullable|numeric|min:0',
		self::FIELD_LINK_RATING => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_LINK_REL => 'nullable|max:255',
		self::FIELD_LINK_RSS => 'required|max:255',
		self::FIELD_LINK_TARGET => 'nullable|max:25',
		self::FIELD_LINK_UPDATED => 'required|date',
		self::FIELD_LINK_URL => 'required|max:760|unique:wp_links,link_url',
		self::FIELD_LINK_VISIBLE => 'nullable|max:20',
	];
	protected $hints = [
		self::FIELD_LINK_ID => 'Unique number assigned to each row of the table.',
		self::FIELD_LINK_URL => 'Unique universal resource locator for the link.',
		self::FIELD_LINK_NAME => 'Name of the link.',
		self::FIELD_LINK_IMAGE => 'URL of an image related to the link.',
		self::FIELD_LINK_TARGET => 'The target frame for the link. e.g. _blank, _top, _none.',
		self::FIELD_LINK_DESCRIPTION => 'Description of the link.',
		self::FIELD_LINK_VISIBLE => 'Control if the link is public or private.',
		self::FIELD_LINK_OWNER => 'ID of user who created the link.',
		self::FIELD_LINK_RATING => 'Add a rating between 0-10 for the link.',
		self::FIELD_LINK_UPDATED => 'Time and date of link update.',
		self::FIELD_LINK_REL => 'Relationship of link.',
		self::FIELD_LINK_NOTES => 'Notes about the link.',
		self::FIELD_LINK_RSS => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
	];
	protected array $relationshipInfo = [
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'link_owner',
			'foreignKey' => WpLink::FIELD_LINK_OWNER,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'link_owner',
			'ownerKey' => WpLink::FIELD_LINK_OWNER,
			'methodName' => 'user',
		],
	];
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, WpLink::FIELD_LINK_OWNER, \App\Models\User::FIELD_ID,
			WpLink::FIELD_LINK_OWNER);
	}
}
