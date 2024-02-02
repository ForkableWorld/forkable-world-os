<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\QMButton;
use App\Buttons\Vote\VoteDeleteButton;
use App\Buttons\Vote\VoteDownButton;
use App\Buttons\Vote\VoteUpButton;
use App\Cards\QMCard;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\Base\BaseVote;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Properties\Vote\VoteValueProperty;
use App\Slim\Middleware\QMAuth;
use App\Storage\DB\QMQB;
use App\Storage\DB\Writable;
use App\Studies\QMUserStudy;
use App\Traits\HasModel\HasGlobalVariableRelationship;
use App\Traits\HasModel\HasCorrelation;
use App\Traits\HasModel\HasUser;
use App\Traits\ModelTraits\IsVote;
use App\UI\CssHelper;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
use App\UI\QMColor;
use App\Utils\UrlHelper;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariableCategory;
use Awobaz\Compoships\Compoships;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * App\Models\Vote
 * @OA\Schema (
 *      definition="Vote",
 *      required={"client_id", "user_id", "cause_variable_id", "effect_variable_id", "value"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="client_id",
 *          description="client_id",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="user_id",
 *          description="ID of User",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="cause_variable_id",
 *          description="ID of the predictor variable",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="effect_variable_id",
 *          description="ID of effect variable",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="value",
 *          description="Value of Vote",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="When the record was first created. Use ISO 8601 datetime format",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="When the record in the database was last updated. Use ISO 8601 datetime format",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 * @property integer $id
 * @property string $client_id
 * @property integer $user_id ID of User
 * @property integer $cause_variable_id ID of the predictor variable
 * @property integer $effect_variable_id ID of effect variable
 * @property integer $value Value of Vote
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|Vote whereId($value)
 * @method static \Illuminate\Database\Query\Builder|Vote whereClientId($value)
 * @method static \Illuminate\Database\Query\Builder|Vote whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Vote whereCauseId($value)
 * @method static \Illuminate\Database\Query\Builder|Vote whereEffectId($value)
 * @method static \Illuminate\Database\Query\Builder|Vote whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|Vote whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Vote whereUpdatedAt($value)
 * @property-read Variable $cause
 * @property-read Variable $effect
 * @property string|null $deleted_at
 * @method static Builder|Vote newModelQuery()
 * @method static Builder|Vote newQuery()
 * @method static Builder|Vote query()
 * @method static Builder|Vote whereDeletedAt($value)
 * @mixin Eloquent
 * @property-read OAClient|null $oa_client
 * @property-read User $user
 * @property-read Variable $variable
 * @method static Builder|Vote whereCauseVariableId($value)
 * @method static Builder|Vote whereEffectVariableId($value)
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read Variable $cause_variable
 * @property-read Variable $effect_variable
 * @property int|null $correlation_id
 * @property int|null $global_variable_relationship_id
 * @property-read GlobalVariableRelationship|null $global_variable_relationship
 * @property-read UserVariableRelationship|null $correlation
 * @property mixed $raw
 * @method static Builder|Vote whereGlobalVariableRelationshipId($value)
 * @method static Builder|Vote whereCorrelationId($value)
 * @property bool|null $is_public
 * @method static Builder|Vote whereIsPublic($value)
 * @property-read OAClient|null $client
 */
class Vote extends BaseVote {
    use HasFactory;

	use SearchesRelations, IsVote, Compoships;
	use HasUser, HasCorrelation, HasGlobalVariableRelationship;
	public const CLASS_CATEGORY             = "Studies";
	public const CLASS_DESCRIPTION          = "Vote thumbs down button for relationships that you think are coincidences and thumbs up for user_variable_relationships with a plausible causal explanation. ";
	public const CLASS_DESCRIPTION_EXTENDED = "I am really good at finding user_variable_relationships and even compensating for various onset delays and durations of action. However, you are much better than me at knowing if there's a way that a given factor could plausibly influence an outcome. You can help me learn and get better at my predictions. " .
	self::CLASS_DESCRIPTION;
	public const COLOR                      = QMColor::HEX_GREEN;
	const        DOWN                       = 'down';
	public const DOWN_VALUE                 = 0;
	public const FONT_AWESOME               = 'far fa-thumbs-up';
	const        NONE                       = "none";
	public const NO_VOTE_IMAGE              = ImageUrls::QUESTION_MARK;
	public const TABLE                      = 'votes';
	public const THUMB_DOWN_BLACK_IMAGE     = 'https://static.quantimo.do/img/thumbs/thumb_down_black.png';
	public const THUMB_DOWN_BLACK_IMAGE_16  = 'https://static.quantimo.do/img/thumbs/thumb_down_black_16.png';
	public const THUMB_DOWN_WHITE_IMAGE     = 'https://static.quantimo.do/img/thumbs/thumb_down_white.png';
	public const THUMB_DOWN_WHITE_IMAGE_16  = 'https://static.quantimo.do/img/thumbs/thumb_down_white_16.png';
	public const THUMB_UP_BLACK_IMAGE       = 'https://static.quantimo.do/img/thumbs/thumb_up_black.png';
	public const THUMB_UP_BLACK_IMAGE_16    = 'https://static.quantimo.do/img/thumbs/thumb_up_black_16.png';
	public const THUMB_UP_WHITE_IMAGE       = 'https://static.quantimo.do/img/thumbs/thumb_up_white.png';
	public const THUMB_UP_WHITE_IMAGE_16    = 'https://static.quantimo.do/img/thumbs/thumb_up_white_16.png';
	const        UP                         = 'up';
	public const UP_VALUE                   = 1;
	const        VIEW_AVERAGE_VOTES         = "average_votes";
	/**
	 * Indicates if the resource should be globally searchable.
	 * @var bool
	 */
	public static $globallySearchable = false;
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [10, 25, 50, 100];
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'cause_variable' => [Variable::FIELD_NAME],
		'effect_variable' => [Variable::FIELD_NAME],
		'user' => [User::FIELD_DISPLAY_NAME],
	];
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = 'id';
	/**
	 * The attributes that should be casted to native types.
	 * @var array
	 */
	protected $casts = [
		"id" => "integer",
		"client_id" => "string",
		"user_id" => "integer",
		"cause_variable_id" => "integer",
		"effect_variable_id" => "integer",
		"value" => "integer",
	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_VALUE => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_CAUSE_VARIABLE_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_EFFECT_VARIABLE_ID => 'nullable|integer|min:1|max:2147483647',
	];
	protected $with = [
		//'cause_variable:'.Variable::IMPORTANT_FIELDS,  // Too complicated and redundant data. Just get relations directly
		//'effect_variable:'.Variable::IMPORTANT_FIELDS,
	];
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		return true;
		// Sometimes user is out of date?  return (bool)QMAuth::getUser()->getNumberOfCorrelations();
	}
	public function getFontAwesome(): string{
		if($this->isUpVote()){
			return FontAwesome::THUMBS_UP;
		}
		if($this->isDownVote()){
			return FontAwesome::THUMBS_DOWN;
		}
		return Vote::FONT_AWESOME;
	}
	public static function getUniqueIndexColumns(): array{
		return [
			self::FIELD_USER_ID,
			self::FIELD_CAUSE_VARIABLE_ID,
			self::FIELD_EFFECT_VARIABLE_ID,
		];
	}

	//    public function getCorrelation(): ?UserVariableRelationship {
	//        return UserVariableRelationship::whereUserId($this->user_id)
	//            ->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $this->getCauseVariableId())
	//            ->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $this->getEffectVariableId())
	//            ->first();
	//    }
	//    public function getGlobalVariableRelationship(): ?GlobalVariableRelationship {
	//        return GlobalVariableRelationship::query()
	//            ->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $this->getCauseVariableId())
	//            ->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $this->getEffectVariableId())
	//            ->first();
	//    }
	public static function createView(){
		$select = "
            select
                   count(v.id) as number_of_votes,
                   avg(v.value) as average_vote,
                   v.cause_variable_id,
                   v.effect_variable_id
            from votes v
            group by v.cause_variable_id, v.effect_variable_id;
        ";
		Writable::createOrReplaceView(self::VIEW_AVERAGE_VOTES, $select);
	}
	/**
	 * @param int $userId
	 * @param $causeName
	 * @param $effectName
	 * @return int
	 */
	public static function deleteVote(int $userId, $causeName, $effectName): int{
		$cause = QMCommonVariable::findByNameOrId($causeName);
		$effect = QMCommonVariable::findByNameOrId($effectName);
		$db = Writable::db();
		return $db->table('votes')->where('user_id', $userId)->where(self::FIELD_CAUSE_VARIABLE_ID, $cause->variableId)
			->where(self::FIELD_EFFECT_VARIABLE_ID, $effect->variableId)->delete();
	}
	/**
	 * @param int $causeVariableId
	 * @param int $effectVariableId
	 * @return array
	 */
	public static function getDownVotes(int $causeVariableId, int $effectVariableId): array{
		return self::readonly()->where(self::TABLE . '.' . self::FIELD_CAUSE_VARIABLE_ID, $causeVariableId)
			->where(self::TABLE . '.' . self::FIELD_EFFECT_VARIABLE_ID, $effectVariableId)->where(self::TABLE . '.' .
				self::FIELD_VALUE, self::DOWN_VALUE)->getFromCacheIfPossible();
	}
	public static function getSlimClass(): string{ return Vote::class; }
	/**
	 * @return string
	 */
	public static function getThumbsDownImageHtml(string $style = null): string{
		return HtmlHelper::getImageHtml(self::THUMB_DOWN_BLACK_IMAGE_16, "Down-Vote", $style);
	}
	/**
	 * @return string
	 */
	public static function getThumbsUpImageHtml(string $style = null): string{
		return HtmlHelper::getImageHtml(self::THUMB_UP_BLACK_IMAGE_16, "Up-Vote", $style);
	}
	/**
	 * @param int $causeVariableId
	 * @param int $effectVariableId
	 * @return array
	 */
	public static function getUpVotes(int $causeVariableId, int $effectVariableId): array{
		return self::readonly()->where(self::TABLE . '.' . self::FIELD_CAUSE_VARIABLE_ID, $causeVariableId)
			->where(self::TABLE . '.' . self::FIELD_EFFECT_VARIABLE_ID, $effectVariableId)->where(self::TABLE . '.' .
				self::FIELD_VALUE, 1)->getFromCacheIfPossible();
	}
	/**
	 * @return QMQB
	 */
	public static function readonly(): QMQB{
		$qb = parent::readonly();
		$qb->join('variables AS cvars', 'votes.cause_variable_id', '=', 'cvars.id')
			->join('variables AS evars', 'votes.effect_variable_id', '=', 'evars.id');
		$qb->columns[] = 'cvars.name as causeVariableName';
		$qb->columns[] = 'evars.name as effectVariableName';
		$qb->columns[] = 'cvars.variable_category_id as causeVariableCategoryId';
		$qb->columns[] = 'evars.variable_category_id as effectVariableCategoryId';
		$qb->columns[] = 'votes.cause_variable_id as causeVariableId';
		$qb->columns[] = 'votes.effect_variable_id as effectVariableId';
		$qb->columns[] = 'votes.correlation_id as correlationId';
		$qb->columns[] = 'votes.global_variable_relationship_id as aggregateCorrelationId';
		$qb->columns[] = 'votes.cause_variable_id as causeId';
		$qb->columns[] = 'votes.effect_variable_id as effectId';
		$qb->columns[] = 'votes.user_id as userId';
		$qb->columns[] = 'votes.value as value';
		$qb->columns[] = 'votes.' . self::FIELD_DELETED_AT . " as deletedAt";
		$qb->columns[] = 'votes.' . self::FIELD_UPDATED_AT . " as createdAt";
		$qb->columns[] = 'votes.' . self::FIELD_CREATED_AT . " as updatedAt";
		$qb->columns[] = 'votes.' . self::FIELD_ID;
		return $qb;
	}
	/**
	 * @param int $userId
	 * @param null $value
	 * @param int|null $causeId
	 * @param int|null $effectId
	 * @return bool|int
	 */
	public static function insertOrUpdateVote(int $userId, $value = null, int $causeId = null, int $effectId = null){
		if($value === null){
			$value = VoteValueProperty::fromRequest();
		}
		if(!$causeId){
			$causeId = BaseCauseVariableIdProperty::fromRequest(true);
		}
		if(!$effectId){
			$effectId = BaseEffectVariableIdProperty::fromRequest(true);
		}
		$existingVote = self::getFromDB($userId);
		if(!empty($existingVote)){
			$success = self::updateVote($value, $existingVote->id);
		} else{
			$success = self::insertVote($userId, $value, $causeId, $effectId);
		}
		if($value === self::DOWN_VALUE){
			// I'm thinking we should keep the user_variable_relationships so it's easier to delete downvotes in the future and find cause of bad user_variable_relationships to prevent them ?
			self::setQMScoreToZero();
		}
		//if (self::getVoteValueFromRequest()) {  This is done in an offline job
		//$study = PopulationStudy::getOrCreateStudy(QMRequest::getCauseVariableId(true), QMRequest::getEffectVariableId(true));
		//$study->publishToWordPressAndMediaWiki();
		//}
		return $success;
	}
	/**
	 * @param int $userId
	 * @return object
	 */
	public static function getFromDB(int $userId): ?object{
		$qb = self::readonly()->where(self::TABLE . '.user_id', $userId)->where(self::TABLE . '.cause_variable_id',
				BaseCauseVariableIdProperty::fromRequest(true))->where(self::TABLE . '.effect_variable_id',
				BaseEffectVariableIdProperty::fromRequest(true));
		$existingVote = $qb->first();
		return $existingVote;
	}
	/**
	 * @param int|null $value
	 * @param int $existingVoteId
	 * @return int
	 */
	public static function updateVote(?int $value, int $existingVoteId): int{
		$data = ['client_id' => BaseClientIdProperty::fromMemory(), 'updated_at' => date('Y-m-d H:i:s')];
		if($value === null){ // Value field can't handle null
			$data[Vote::FIELD_DELETED_AT] = date('Y-m-d H:i:s');
		} else{
			$data[Vote::FIELD_VALUE] = $value;
		}
		return self::writable()->where('id', $existingVoteId)->update($data);
	}
	/**
	 * @param int $userId
	 * @param $value
	 * @param $causeId
	 * @param $effectId
	 * @return bool
	 */
	public static function insertVote(int $userId, $value, $causeId, $effectId): bool{
		return self::writable()->insert([
			'user_id' => $userId,
			'client_id' => BaseClientIdProperty::fromMemory(),
			self::FIELD_CAUSE_VARIABLE_ID => $causeId,
			self::FIELD_EFFECT_VARIABLE_ID => $effectId,
			'value' => $value,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		]);
	}
	/**
	 * @return int
	 */
	public static function setQMScoreToZero(): int{
		return QMUserVariableRelationship::writable()->where('user_id', QMAuth::getQMUser()->id)
			->where(self::FIELD_CAUSE_VARIABLE_ID, BaseCauseVariableIdProperty::fromRequest(true))
			->where(self::FIELD_EFFECT_VARIABLE_ID, BaseEffectVariableIdProperty::fromRequest(true))->update([
				UserVariableRelationship::FIELD_QM_SCORE => 0,
				UserVariableRelationship::FIELD_UPDATED_AT => now_at(),
			]);
		// DON'T DO THIS BECAUSE WE DON'T WANT TO DELETE THIRD-PARTY studies
		//        AggregatedCorrelation::writable()
		//            ->where(self::FIELD_CAUSE_VARIABLE_ID, $this->getCauseVariableId())
		//            ->where(self::FIELD_EFFECT_VARIABLE_ID, $this->getCauseVariableId())
		//            ->delete();
	}
	public function downVoted(): bool{
		if($this->value === null){
			return false;
		}
		return $this->value === Vote::DOWN_VALUE;
	}
	/**
	 * @return QMButton[]
	 */
	public function getButtons(): array{
		$buttons = [];
		$buttons[] = $this->getDownVoteButton();
		$buttons[] = $this->getUpVoteButton();
		return $buttons;
	}
	/**
	 * @param bool $includeText
	 * @return QMButton
	 */
	public function getDownVoteButton(bool $includeText = true){
		$text = $includeText ? "Seems Coincidental" : "";
		$url = $this->getDownVoteUrl();
		$params = $this->getUrlParams();
		$button =
			new VoteDownButton($url, $params, $this->getCauseVariableName(), $this->getEffectVariableName(), $text);
		return $button;
	}
	/**
	 * @return string
	 */
	public function getDownVoteUrl(): string{
		$url = self::generateVoteUrl($this->getUrlParams());
		$url = UrlHelper::addParam($url, 'vote', 'down');
		return $url;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public static function generateVoteUrl(array $params): string{
		$url = UrlHelper::getApiUrlForPath('votes', $params, 'app.quantimo.do');
		//return UrlHelper::addAccessTokenToUrl($url); // This is dangerous!
		$userId = QMAuth::id(false);
		if($userId){
			$url = UrlHelper::addParams($url, ['userId' => $userId]);
		}
		return $url;
	}
	/**
	 * @return array
	 */
	private function getUrlParams(): array{
		$params = [];
		$params['causeVariableId'] = $this->getCauseVariableId();
		$params['effectVariableId'] = $this->getEffectVariableId();
		$params['userId'] = $this->getUserId();
		return $params;
	}
	/**
	 * @return int
	 */
	public function getCauseVariableId(): int{
		return $this->cause_variable_id;
	}
	/**
	 * @return int
	 */
	public function getEffectVariableId(): int{
		return $this->effect_variable_id;
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		return $this->user_id;
	}
	/**
	 * @param bool $includeText
	 * @return QMButton
	 */
	public function getUpVoteButton(bool $includeText = true){
		$text = $includeText ? "Seems Plausible" : "";
		$url = $this->getUpVoteUrl();
		$params = $this->getUrlParams();
		$button = new VoteUpButton($url, $params, $this->getCauseVariableName(), $this->getEffectVariableName(), $text);
		return $button;
	}
	/**
	 * @return string
	 */
	public function getUpVoteUrl(): string{
		$url = self::generateVoteUrl($this->getUrlParams());
		$url = UrlHelper::addParam($url, 'vote', 'up');
		return $url;
	}
	public function upVoted(): bool{
		if($this->value === null){
			return false;
		}
		return $this->value === Vote::UP_VALUE;
	}
	public function findGlobalVariableRelationship(): ?GlobalVariableRelationship{
		return GlobalVariableRelationship::findByData([
			GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID => $this->getCauseVariableId(),
			GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID => $this->getEffectVariableId(),
		]);
	}
	public function getCorrelation(): ?UserVariableRelationship
    {
		return $this->l()->getCorrelation();
	}
	/**
	 * @return BaseModel|Vote
	 */
	public function l(): Vote{
		return parent::l();
	}
	/**
	 * @param int $width
	 * @param int $height
	 * @return string
	 */
	public function getThumbImageHtml(int $width = 10, int $height = 10): string{
		$url = $this->getThumbImage();
		return HtmlHelper::getImageHtmlWithSize($url, $width, $height, $this->getTooltip());
	}
	/**
	 * @return string
	 */
	public function getTooltip(): string{
		if($this->getValue() === 1){
			return "Up-Voted";
		}
		if($this->getValue() === 0){
			return "Down-Voted";
		}
		return "Needs Review";
	}
	public function getUserStudy(): QMUserStudy{
		$study =
			QMUserStudy::findOrNewQMStudy($this->getCauseVariableId(), $this->getEffectVariableId(), $this->getUserId(),
				StudyTypeProperty::TYPE_INDIVIDUAL);
		return $study;
	}
	public function getCard(): QMCard{
		$card = new QMCard($this->getId());
		$card->setHeaderTitle($this->getListCardTitle());
		$card->setContent($this->getListCardSubTitle());
		$card->setAvatar($this->getThumbImage());
		//$card->setImage($this->getImage());
		if($this->isUpVote()){
			$card->addActionSheetButton($this->getDeleteVoteButton());
			$card->addActionSheetButton($this->getDownVoteButton());
		} elseif($this->isDownVote()){
			$card->addActionSheetButton($this->getDeleteVoteButton());
			$card->addActionSheetButton($this->getUpVoteButton());
		} else{
			$card->addActionSheetButton($this->getDownVoteButton());
			$card->addActionSheetButton($this->getUpVoteButton());
		}
		return $card;
	}
	/**
	 * @return string
	 */
	public function getListCardTitle(): string{
		if($this->getValue() === 1){
			$title = "Up-Voted";
		} elseif($this->getValue() === 0){
			$title = "Down-Voted";
		} else{
			$title = "Have Not Voted";
		}
		return $title;
	}
	/**
	 * @return string
	 */
	public function getListCardSubTitle(): string{
		return $this->getCauseVariableName() . " Could Influence " . $this->getEffectVariableName();
	}
	/**
	 * @return string
	 */
	public function getThumbImage(): string{
		if($this->getValue() === 1){
			return self::THUMB_UP_BLACK_IMAGE;
		}
		if($this->getValue() === 0){
			return self::THUMB_DOWN_BLACK_IMAGE;
		}
		return self::NO_VOTE_IMAGE;
	}
	/**
	 * @param bool $includeText
	 * @return QMButton
	 */
	private function getDeleteVoteButton(bool $includeText = true){
		$text = $includeText ? "Uncertain if there's a relationship" : "";
		$url = $this->getDeleteVoteUrl();
		$params = $this->getUrlParams();
		$button =
			new VoteDeleteButton($url, $params, $this->getCauseVariableName(), $this->getEffectVariableName(), $text);
		return $button;
	}
	/**
	 * @return string
	 */
	public function getDeleteVoteUrl(): string{
		$url = self::generateVoteUrl($this->getUrlParams());
		$url = UrlHelper::addParam($url, 'vote', self::NONE);
		return $url;
	}
	/**
	 * @return string
	 */
	public function getHtmlContent(): string{
		$html = '
            <div style="text-align: center;">
                ' . $this->getTitleHtml() . $this->getVariableImages() . '
            </div>
            <br>
        ';
		return $html;
	}
	/**
	 * @return string
	 */
	public function getTitleHtml(): string{
		$title = '';
		if($this->getValue()){
			$title .= "Up-Voted ";
		} elseif($this->getValue() === 0){
			$title .= "Down-Voted ";
		}
		$title .= $this->getCauseVariableName() . ' Affects ' . $this->getEffectVariableName();
		$title = CssHelper::addTitleCss($title);
		return $title;
	}
	/**
	 * @return int
	 */
	public function getValue(): ?int{
		return $this->value;
	}
	/**
	 * @return string
	 */
	public function getCauseVariableName(): string{
		return $this->getCauseVariable()->name;
	}
	/**
	 * @return string
	 */
	public function getEffectVariableName(): string{
		return$this->getEffectVariable()->name;
	}
	/**
	 * @return string
	 */
	private function getVariableImages(): string{
		return '<div id="variable-images" style="justify-content:space-around;">
            <div style="display: inline-block; max-width: 45%;">
                <img style="max-width: 100%;" src="' . $this->getCauseVariableSvgUrl() . '" alt="cause image">
            </div>
            <div style="display: inline-block; max-width: 45%;">
                <img style="max-width: 100%;" src="' . $this->getEffectVariableSvgUrl() . '" alt="effect image">
            </div>
        </div>';
	}
	/**
	 * @return string
	 */
	public function getCauseVariableSvgUrl(): string{
		return $this->getCauseVariableCategory()->getSvgUrl();
	}
	/**
	 * @return QMVariableCategory
	 */
	public function getCauseVariableCategory(): QMVariableCategory{
		return QMVariableCategory::find($this->getCauseVariableCategoryId());
	}
	/**
	 * @return int
	 */
	public function getCauseVariableCategoryId(): int{
		return $this->getCauseVariable()->variable_category_id;
	}
	/**
	 * @return string
	 */
	public function getEffectVariableSvgUrl(): string{
		return $this->getEffectVariableCategory()->getSvgUrl();
	}
	/**
	 * @return QMVariableCategory
	 */
	public function getEffectVariableCategory(): QMVariableCategory{
		return QMVariableCategory::find($this->getEffectVariableCategoryId());
	}
	/**
	 * @return int
	 */
	public function getEffectVariableCategoryId(): int{
		return $this->getEffectVariable()->variable_category_id;
	}
	public function getImage(): string{
		if($this->isUpVote()){
			return self::THUMB_UP_BLACK_IMAGE;
		}
		if($this->isDownVote()){
			return self::THUMB_DOWN_BLACK_IMAGE;
		}
		return self::NO_VOTE_IMAGE;
	}
	public function getSubtitleAttribute(): string{
		return Vote::CLASS_DESCRIPTION;
	}
}
