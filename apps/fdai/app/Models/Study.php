<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Buttons\RelationshipButtons\Study\StudyCauseVariableButton;
use App\Buttons\RelationshipButtons\Study\StudyEffectVariableButton;
use App\Cards\QMCard;
use App\Cards\StudyCard;
use App\VariableRelationships\QMVariableRelationship;
use App\Exceptions\InvalidStringException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoIdException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UnauthorizedException;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Mail\QMSendgrid;
use App\Mail\StudyReportQMEmail;
use App\Mail\TooManyEmailsException;
use App\Menus\QMMenu;
use App\Models\Base\BaseStudy;
use App\Notifications\StudyPublished;
use App\Properties\Base\BasePostStatusProperty;
use App\Properties\BaseProperty;
use App\Properties\Study\StudyIdProperty;
use App\Properties\Study\StudyIsPublicProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Properties\User\UserIdProperty;
use App\Reports\StudyReport;
use App\Repos\StudiesRepo;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Storage\S3\S3Private;
use App\Studies\QMPopulationStudy;
use App\Studies\QMStudy;
use App\Studies\StudyHtml;
use App\Studies\StudyImages;
use App\Studies\StudyLinks;
use App\Studies\StudySection;
use App\Studies\StudyText;
use App\Traits\HasButton;
use App\Traits\HasCauseAndEffect;
use App\Traits\HasCorrelationCoefficient;
use App\Traits\HasDBModel;
use App\Traits\HasFiles;
use App\Traits\HasModel\HasGlobalVariableRelationship;
use App\Traits\HasModel\HasUser;
use App\Traits\HasName;
use App\Traits\HasVotes;
use App\Traits\PostableTrait;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\QMColor;
use App\Utils\AppMode;
use App\Utils\UrlHelper;
use Awobaz\Compoships\Compoships;
use Awobaz\Compoships\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;
use SendGrid\Mail\TypeException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;
use Titasgailius\SearchRelations\SearchesRelations;

/**
 * Class Study
 * @property string $id
 * @property string $type
 * @property int $cause_variable_id
 * @property int $effect_variable_id
 * @property int $user_id
 * @property Carbon $created_at
 * @property string $deleted_at
 * @property string $analysis_parameters
 * @property string $user_study_text
 * @property string $user_title
 * @property string $study_status
 * @property string $comment_status
 * @property string $study_password
 * @property string $study_images
 * @property string $errors
 * @property Carbon $updated_at
 * @property string $client_id
 * @property Carbon $published_at
 * @property int $wp_post_id
 * @property Carbon $newest_data_at
 * @property Carbon $analysis_requested_at
 * @property string $reason_for_analysis
 * @property Variable $variable
 * @package App\Models
 * @property-read User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|Study newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Study newQuery()
 * @method static Builder|Study onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Study query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereAnalysisParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereAnalysisRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereCauseVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereCommentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereEffectVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereErrors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereNewestDataAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereReasonForAnalysis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereStudyImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereStudyPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereStudyStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereUserStudyText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereUserTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereWpPostId($value)
 * @method static Builder|Study withTrashed()
 * @method static Builder|Study withoutTrashed()
 * @mixin Eloquent
 * @property-read OAClient|null $oa_client
 * @property-read Variable $cause_variable
 * @property-read Variable $effect_variable
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel nPerGroup($group, $n = 10)
 * @property string|null $analysis_ended_at
 * @property string|null $analysis_started_at
 * @property string|null $internal_error_message
 * @property string|null $user_error_message
 * @property string|null $status
 * @property string|null $analysis_settings_modified_at
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereAnalysisEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereAnalysisSettingsModifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereAnalysisStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereInternalErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereUserErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel excludeLargeColumns()
 * @property bool|null $is_public
 * @property int $sort_order
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereSortOrder($value)
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property-read OAClient|null $client
 * @method static \Illuminate\Database\Eloquent\Builder|Study whereSlug($value)
 */
class Study extends BaseStudy implements HasMedia {
    use HasFactory;

	use SoftDeletes, HasDBModel, HasGlobalVariableRelationship;
	use HasVotes;
	use Compoships;
	use SearchesRelations;
	use HasCauseAndEffect, HasUser, PostableTrait, HasButton, HasName;
	use PostableTrait, HasFiles;

	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = 'id';
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [//'id',
	];
	public static $group = Study::CLASS_CATEGORY;
	//public $with = ['user'];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'user' => [User::FIELD_DISPLAY_NAME],
	];
	public static function getSlimClass(): string{ return QMStudy::class; }
	public const CLASS_DESCRIPTION = 'Study Analysis Settings Saved by Individuals';
	public const CLASS_CATEGORY = WpPost::PARENT_CATEGORY_STUDIES;
	public const FONT_AWESOME = 'fas fa-university';
	public const COLOR = QMColor::HEX_RED;
	public static function getUniqueIndexColumns(): array{
		return [
			self::FIELD_TYPE,
			self::FIELD_USER_ID,
			self::FIELD_CAUSE_VARIABLE_ID,
			self::FIELD_EFFECT_VARIABLE_ID,
		];
	}
	public $incrementing = false;
	public const COMMENT_STATUS_OPEN = "open";
	public const COMMENT_STATUS_CLOSED = "closed";
	protected $hidden = [
		'study_password',
	];
	protected array $openApiSchema = [
		self::FIELD_ANALYSIS_PARAMETERS => ['type' => 'array', 'items' => ['type' => 'object']],
	];
	protected $casts = [
		self::FIELD_CAUSE_VARIABLE_ID => 'int',
		self::FIELD_EFFECT_VARIABLE_ID => 'int',
		self::FIELD_USER_ID => 'int',
		self::FIELD_WP_POST_ID => 'int',
		self::FIELD_ANALYSIS_PARAMETERS => 'array',
		self::FIELD_STUDY_IMAGES => 'object',
	];
	protected array $rules = [
		self::FIELD_ANALYSIS_REQUESTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_CAUSE_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_COMMENT_STATUS => 'in:open,closed',
		self::FIELD_EFFECT_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_NEWEST_DATA_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_PUBLISHED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_REASON_FOR_ANALYSIS => 'nullable|max:255',
		self::FIELD_STUDY_PASSWORD => 'nullable|max:20',
		self::FIELD_STUDY_STATUS => 'in:draft,publish,private',
		self::FIELD_TYPE => 'required|max:20',
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_USER_STUDY_TEXT => 'nullable',
		self::FIELD_USER_TITLE => 'nullable|max:65535',
		self::FIELD_WP_POST_ID => 'nullable|integer|min:1|max:2147483647',
	];
    public function getDebugUrl(): string{
		return UrlHelper::toLocalUrl($this->getAnalyzeUrl());
	}
	public function getReport(): StudyReport{
		return new StudyReport($this);
	}
	/**
	 * @return string
	 */
	public function getUniqueIndexIdsSlug(): string{
		return StudyIdProperty::generateStudyId($this->getCauseVariableId(), $this->getEffectVariableId(),
			$this->user_id, $this->type);
	}
	/**
	 * @return void
	 */
	public function publish(): void{
		$this->getDBModel()->publish();
	}
	/**
	 * @return QMStudy|HasCauseAndEffect
	 */
	public function getDBModel(): DBModel{
		if($this->hasId()){
			$study = QMStudy::findInMemory($this->getId());
			if($study){
				if((bool)$study->isPublic !== (bool)$this->is_public){
					le("is_public is wrong!");
				}
				return $study;
			}
		}
		$study = QMStudy::convertRowToModel($this);
		$study->setLaravelModel($this);
		if((bool)$study->isPublic !== (bool)$this->is_public){
			le('is_public is wrong!');
		}
		return $study;
	}
	/**
	 * @return BaseProperty[]
	 */
	public function getModifiedPropertyModels(): array{
		return parent::getModifiedPropertyModels();
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		return StudyLinks::generateStudyLinkStatic($this->getStudyId(), $params);
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		if(!$this->hasId()){
			return static::getClassNameTitle();
		}
		return $this->getOrSetQMStudy()->getTitleAttribute();
	}
	/**
	 * @return string
	 */
	public function getSubtitleAttribute(): string{
		if(!$this->hasId()){
			return static::CLASS_DESCRIPTION;
		}
		return $this->getStudyText()->getTagLine();
	}
	/**
	 * @return QMVariableRelationship|HasCorrelationCoefficient
	 * @throws NotEnoughDataException
	 */
	public function getHasCorrelationCoefficient(){
		$hasCauseAndEffect = $this->getOrSetQMStudy();
		return $hasCauseAndEffect->getHasCorrelationCoefficient();
	}
	public function getCauseVariable(): Variable{
		return $this->cause_variable;
	}
	public function getEffectVariable(): Variable{
		return $this->effect_variable;
	}
	public function getEditUrl(array $params = []): string{
		return StudyLinks::generateStudyUrlDynamic($this->getCauseVariableId(), $this->getEffectVariableId());
	}
	public static function newFake(int $userId = UserIdProperty::USER_ID_TEST_USER): BaseModel{
		$m = parent::newFake();
		$c = UserVariableRelationship::firstOrFakeSave();
		$m->id = $c->getStudyId();
		$m->cause_variable_id = $c->getCauseVariableId();
		$m->effect_variable_id = $c->getEffectVariableId();
		$m->user_id = $c->getUserId();
		return $m;
	}
	/**
	 * @param $data
	 * @return Study|null
	 */
	public static function findByData($data): ?BaseModel{
		$id = StudyIdProperty::pluckOrDefault($data);
		return static::findInMemoryOrDB($id);
	}
	/**
	 * @param $data
	 * @return Study
	 */
	public static function new($data): BaseModel{
		return parent::new($data);
	}
	/**
	 * @param $data
	 */
	public function populateForeignKeys($data){
		$this->id = StudyIdProperty::pluckOrDefault($data);
		parent::populateForeignKeys($data);
	}
	public function save(array $options = []): bool{
		$data = $this->toArray();
		if($this->getStudyType() === StudyTypeProperty::TYPE_COHORT){
			$client = OAClient::findOrCreate($data);
		}
		return parent::save($options);
	}
	public function getClientId(): ?string{
		if($this->type === StudyTypeProperty::TYPE_COHORT){
			$studyId = $this->getId();
			$previousClientId = $this->attributes[self::FIELD_CLIENT_ID] ?? null;
			if($studyId !== $previousClientId){
				$this->setAttribute(self::FIELD_CLIENT_ID, $studyId);
			}
		}
		return $this->attributes[self::FIELD_CLIENT_ID] ?? null;
	}
	public function getClientIdAttribute(): ?string{
		return $this->getClientId();
	}
	public function toArray(): array{
		$data = parent::toArray();
		if($this->type === StudyTypeProperty::TYPE_COHORT){
			$data[self::FIELD_CLIENT_ID] = $this->getClientId();
		}
		return $data;
	}
	public function getCauseVariableCategoryId(): int{
		return $this->getCauseVariable()->getVariableCategoryId();
	}
	public function getEffectVariableCategoryId(): int{
		return $this->getEffectVariable()->getVariableCategoryId();
	}
	public function getCauseVariableId(): int{
		return $this->attributes[self::FIELD_CAUSE_VARIABLE_ID];
	}
	public function getEffectVariableId(): int{
		return $this->attributes[self::FIELD_EFFECT_VARIABLE_ID];
	}
	public function getStudyType(): ?string{
		return $this->attributes[self::FIELD_TYPE] ?? null;
	}
	/**
	 * @return HasCorrelationCoefficient|null
	 */
	public function getHasCorrelationCoefficientIfSet() {
        if(!$this->type && !$this->relations){
            return null;
        }
		return $this->getDBModel()->getHasCorrelationCoefficientIfSet();
	}
	public function getFileUrls(): array{return [];}
	public function hasIds(): bool{
		return !empty($this->attributes);
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	public function getShowContentView(array $params = []): View{
		return $this->getDBModel()->getShowContentView($params);
	}
	protected function getShowPageView(array $params = []): View{
		return $this->getDBModel()->getShowContentView($params);
	}

    /**
     * @return View
     */
    protected static function getIndexPageView(): View{
		return view('studies-index', [
			'buttons' => static::generateIndexButtons(),
			'heading' => "Individual Case Studies",
		]);
	}
	/**
	 * Have to override parent because middleware filter won't work with studies
	 * Population studies don't need auth check
	 * but individual studies do
	 * TODO: Split up population and individual studies
	 */
	public function authorizeView(){
		if($this->typeIsPopulation()){
			return;
		}
		if(!AppMode::isApiRequest()){
			return;
		}
		/** @var User $user */
		$user = \Auth::user();
		if(!$user){
			$user = QMAuth::getUser();
		}
		if($this->is_public){
			return;
		}
		if(!$user){
			throw new UnauthorizedException();
		}
		//if(!$user){return;} // If a user were required, they would have been stopped by middleware
		if(!$user->can('view', $this)){
			throw new UnauthorizedException();
		}
	}
	public function getFields(): array{
		$fields = [];
		$fields[] = $this->imageField()->hideFromDetail();
		$fields[] = $this->imageField()->stacked()->onlyOnDetail();
		$fields[] = $this->nameLinkToShowField();
		$fields[] = $this->studyLinkField()->hideFromIndex();
		$fields = array_merge($fields, $this->getShowablePropertyFields());
		return $fields;
	}
	/**
	 * @return GlobalVariableRelationship
	 * @throws NotEnoughDataException
	 */
	public function findGlobalVariableRelationship(): ?GlobalVariableRelationship{
		return $this->getHasCorrelationCoefficient()->findGlobalVariableRelationship();
	}
	public function setIsPublicAttribute(bool $value){
		$this->attributes[self::FIELD_IS_PUBLIC] = $value;
	}
	public function wp_post(): ?WpPost{
		return $this->findWpPost();
	}
	public function getIsPublic(): ?bool{
		return $this->is_public;
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{ return true; }
	/**
	 * @param array|object $data
	 */
	public function populate($data): void{
		parent::populate($data);
		if(!$this->type){
			$this->type = StudyTypeProperty::fromDataOrRequest($data);
		}
		$this->is_public = StudyIsPublicProperty::calculate($this);
	}
	public function getShowView(array $params = []): View{
		$params['content'] = $this->getShowContent();
		if($this->typeIsIndividual()){
			return view('user-study', $this->getShowParams($params));
		}
		return view('population-study', $this->getShowParams($params));
	}
	/**
	 * @inheritDoc
	 */
	public static function wherePostable(){
		$qb = static::query();
		$qb->where(static::TABLE . '.' . static::FIELD_STUDY_STATUS, BasePostStatusProperty::STATUS_PUBLISH);
		return $qb;
	}
	/**
	 * @param int $id
	 */
	public function setWpPostIdAndSave(int $id){
		$this->setAttribute(Study::FIELD_WP_POST_ID, $id);
		$this->setPublishedAtAttribute(now_at());
		if($this->getIsPublic()){
			$this->setAttribute(Study::FIELD_STUDY_STATUS, BasePostStatusProperty::STATUS_PUBLISH);
		} else{
			$this->setAttribute(Study::FIELD_STUDY_STATUS, BasePostStatusProperty::STATUS_PRIVATE);
		}
		try {
			$this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
	}
	/**
	 * @param mixed $publishedAt
	 */
	public function setPublishedAtAttribute(string $publishedAt){
		$this->attributes[self::FIELD_PUBLISHED_AT] = $publishedAt;
		/** @var QMVariableRelationship $s */
		if($s = $this->getHasCorrelationCoefficientIfSet()){
			$s->setPublishedAtAttribute($publishedAt);
		}
	}
	public function exceptionIfWeShouldNotPost(): void{
		/** @var QMVariableRelationship $s */
		if($s = $this->getHasCorrelationCoefficientIfSet()){
			$s->exceptionIfWeShouldNotPost();
		}
		$this->getUser()->exceptionIfWeShouldNotPost();
	}
	/**
	 * @return string
	 */
	public function getCategoryName(): string{
		return ucfirst($this->getTypeHumanized());
	}
	/**
	 * @return string
	 */
	public function getParentCategoryName(): ?string{
		return Study::CLASS_CATEGORY;
	}
	/**
	 * @return string
	 */
	public function generatePostContent(): string{
		$sHtml = $this->getStudyHtml();
		$html = $sHtml->generatePostContent();
		try {
			QMStr::assertStringDoesNotContain($html, "time-chart.png\" alt", __FUNCTION__, false,
                "FullStudyHtmlWithEmbeddedCharts should not contain linked charts! ");
		} catch (InvalidStringException $e) {
			le($e);
		}
		return $html;
	}
	protected static function generateIndexButtons(): array{
		return QMPopulationStudy::generateIndexButtons();
	}
	protected static function getUrlFolder(): string{
		return "studies";
	}
	public function getInteractiveStudyUrl(): string{
		$url = StudyLinks::generateStudyUrlDynamic($this->getCauseVariableName(), $this->getEffectVariableName(),
			$this->getUserId(), $this->getStudyId());
		return $url;
	}
	public function getStudyId(): string{
		return $this->getId();
	}
	public function getFontAwesome(): string{
		return Study::FONT_AWESOME;
	}
	public function getSlugWithNames(): string{
		return $this->getId();
	}
	/**
	 * @return bool
	 * @deprecated
	 */
	public function weShouldPost(): bool{
		$c = $this->getHasCorrelationCoefficientIfSet();
		if(!$c){
			return false;
		}
		return $c->weShouldPost();
	}
	/**
	 * @return array
	 */
	public function getTags(): array{
		$tags = [];
		try {
			if($statistics = $this->getHasCorrelationCoefficientIfSet()){
				return $statistics->getTags();
			}
		} catch (NotEnoughDataException $e) {
		}
		//$tags[] = $this->getCauseVariableDisplayName(); TODO: Uncomment after adding suffix to study variable names
		//$tags[] = $this->getEffectVariableDisplayName();
		//$tags[] = $this->getCauseQMVariableCategory()->getName();
		//$tags[] = $this->getEffectQMVariableCategory()->getName();
		// Don't need principalInvestigator tag until we have multiple investigators
		//if($this->principalInvestigator){$tags[] = $this->getPrincipalInvestigator()->getDisplayNameAttribute();}
		$tags[] = $this->getTypeHumanized();
		$title = [];
		foreach($tags as $tag){
			$title[] = QMStr::titleCaseSlow($tag);
		}
		$title = QMArr::arrayUniqueCaseInsensitive($title);
		return $title;
	}
	public function getCategoryNames(): array{
		return [
			$this->getCauseQMVariableCategory()->name,
			$this->getEffectQMVariableCategory()->name,
		];
	}
	public function getInlineCharts(): ?string{
		try {
			return $this->getOrSetCharts()->getInline();
		} catch (NotEnoughDataException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
			return null;
		}
	}
	public function getIcon(): string{
		return ImageUrls::SCIENCE_FLASK_1;
	}
	public function getShowUrl(array $params = []): string{
		return $this->getUrl($params);
	}
	public function getInvalidSourceData(): array{
		try {
			$c = $this->getOrCreateStatistics();
		} catch (NotEnoughDataException $e) {
			return $this->invalidSourceData = [];
		}
		return $c->getInvalidSourceData();
	}
	public function getVariableSettingsLink(): string{
		$cause = $this->getCauseQMVariable();
		$effect = $this->getEffectQMVariable();
		return $cause->getVariableSettingsLink() . "\n" . $effect->getVariableSettingsLink();
	}
	/**
	 * @return string
	 * @throws TooSlowToAnalyzeException
	 */
	public function getStaticContent(): string{
		return $this->getStudyHtml()->setWithEmbeddedCharts();
	}
	public function getShowContent(bool $inlineJs = false): string{
		$studyHtml = $this->getStudyHtml();
		return $studyHtml->getShowContent();
	}
	public function uploadStudyHtmlAndGetUrl(string $folder): string{
		return S3Private::uploadHTML($folder . '/' . $this->getS3FilePath(), $this->getHtmlPage());
	}
	public function getIonIcon(): string{
		return IonIcon::study;
	}
	public function setTrackingInstructionsIfNecessary(){
		$this->getCauseQMVariable()->getTrackingInstructionCard();
		$this->getEffectQMVariable()->getTrackingInstructionCard();
	}
	public function getHtmlPage(bool $inlineJs = false): string{
		//$post = $this->firstWpPost();
		//if($post){return $post->getHtmlHeadBody();}
		return $this->getStudyHtml()->getFullStudyHtml($inlineJs);
	}
	/**
	 * @return string
	 */
	public function getCategoryDescription(): string{
		return GlobalVariableRelationship::CLASS_DESCRIPTION;
	}
	/**
	 * @param bool $arrows
	 * @param bool $hyperLinkNames
	 * @return string
	 */
	public function getStudyTitle(bool $arrows = false, bool $hyperLinkNames = false): string{
		try {
			$c = $this->getHasCorrelationCoefficient();
			if($c && $c->getCorrelationCoefficient() !== null){
				$title = $c->getPredictorExplanationTitle($arrows, $hyperLinkNames);
				return StudyText::formatTitle($c, $title, $arrows);
			}
		} catch (NotEnoughDataException $e) {
			ConsoleLog::info(__METHOD__ . " Falling back to getStudyQuestion because: " . $e->getMessage());
		}
		return $this->getStudyQuestion();
	}
	public function getContent(): string{
		return $this->getShowContent();
	}
	/**
	 * @return string
	 */
	public function getImage(): string{
		if(!$this->hasIds()){
			return Study::DEFAULT_IMAGE;
		}
		$si = $this->getStudyImages();
		$img = $si->getImage();
		return $img;
	}
	/**
	 * @return StudyImages
	 */
	public function getStudyImages(): StudyImages{
		return new StudyImages($this->getHasCorrelationCoefficientIfSet(), $this->getOrSetQMStudy());
	}
	/**
	 * @param string|null $pageFilename
	 */
	public function deleteJekyllMDFiles(string $pageFilename = null){
		if($pageFilename === null){  // Sometimes toSlug doesn't match the existing file for some reason
			$pageFilename = $this->getUniqueIndexIdsSlug() . '.md';
		}
		$postFolder = $this->getJekyllPostsFolder();
		$pattern = "$postFolder*$pageFilename";
		$this->logInfo("Deleting posts files with name like $pattern");
		$found = false;
		foreach(glob($pattern) as $f){
			$this->logInfo("Deleting $f");
			unlink($f); // Delete posts with same slug but different date
			$found = true;
		}
		if(!$found){
			$this->logInfo("No files found like $pattern");
		}
	}
	public function deleteJekyllChartFiles(){
		$dirPath = StudiesRepo::getAbsolutePath('images/charts/');
		if($this->typeIsIndividual()){
			$dirPath .= $this->getUserId() * 2 . '/';
		} else{
			$dirPath .= 'population/';
		}
		$dirPath .= $this->getId() . '/';
		try {
			FileHelper::deleteDir($dirPath);
		} catch (Throwable $e) {
			QMLog::info($e->getMessage() . ".  Continuing...");
		}
	}
	/**
	 * @return string
	 */
	public function getJekyllPostsFolder(): string{
		$postFolder = StudiesRepo::getAbsolutePath('_posts/');
		return $postFolder;
	}
	public function getCard(): QMCard{
		return $this->getStudyCard();
	}
	/**
	 * @return string
	 */
	public function getNamesSlug(): string{
		$slug = 'effect-of-' . $this->getCauseVariableName() . '-on-' . $this->getEffectVariableName() . '-for-' .
			$this->getType();
		if($this->typeIsIndividual()){
			$slug .= '-' . $this->getUserId();
		}
		if($this->getType() === StudyTypeProperty::TYPE_COHORT){
			$slug .= $this->getId();
		}
		return QMStr::slugify($slug);
	}
	/**
	 * @return bool
	 */
	public function typeIsIndividual(): bool{
		return $this->getType() === StudyTypeProperty::TYPE_INDIVIDUAL;
	}
	/**
	 * @return bool
	 */
	public function typeIsPopulation(): bool{
		return $this->getType() === StudyTypeProperty::TYPE_POPULATION;
	}
	public function getType(): string{
		return $this->getStudyType();
	}
	public function getBody(): string{
		return $this->getStudyHtml()->getShowContent();
	}
	/**
	 * @return string
	 */
	public function getJekyllTagsSection(): string{
		$string = '
tags:';
		foreach($this->getTags() as $tag){
			$string .= '
    - ' . $tag;
		}
		return $string;
	}
	/**
	 * @return StudyReportQMEmail
	 */
	public function email(){
		$email = $this->getEmail();
		try {
			$email->send();
			return $email;
		} catch (TooManyEmailsException | TypeException $e) {
			le($e);
		}
		/** @var LogicException $e */
		throw $e;
	}
	/**
	 * @return StudyReportQMEmail
	 */
	public function getEmail(): QMSendgrid{
		$r = new StudyReportQMEmail($this, UserIdProperty::USER_ID_MIKE);
		return $r;
	}
	/**
	 * @return string
	 */
	public function getEmailBody(): string{
		$m = $this->getEmail();
		return $m->getHtmlContent();
	}
	public function getEmailHtml(): string{
		return $this->getEmail()->getHtmlContent();
	}
	/**
	 * @return string
	 */
	public function getPlainText(): string{
		$text = '';
		$sections = $this->getStudySectionsArray();
		foreach($sections as $section){
			$text .= "\n" . $section->title . "\n";
			$text .= "\n" . $section->body . "\n";
		}
		return $text;
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getStaticStudyButtonHtml(): string{
		$studyHtml = $this->getStudyHtml();
		return $studyHtml->getStaticStudyButton();
	}
	public function saveHtml(): void{
		$this->saveToRepo();
	}
	/**
	 * @return StudySection[]
	 */
	public function getStudySectionsArray(): array{
		return $this->getStudyText()->getStudySectionsArray();
	}
	public function getTagLine(): string{
		return $this->getStudyText()->getTagLine();
	}
	/**
	 * @return string
	 */
	public function getTypeHumanized(): string{
		if($this->getStudyType() === StudyTypeProperty::TYPE_INDIVIDUAL){
			return WpPost::CATEGORY_INDIVIDUAL_CASE_STUDIES;
		}
		if($this->getStudyType() === StudyTypeProperty::TYPE_COHORT){
			return WpPost::CATEGORY_COHORT_GROUP_STUDIES;
		}
		return WpPost::CATEGORY_GLOBAL_POPULATION_STUDIES;
	}
	/**
	 * @return string $studyLinkStatic
	 */
	public function getStudyLinkStatic(array $params = []): string{
		return StudyLinks::generateStudyLinkStatic($this->getStudyId(), $params);
	}
	public function getSharingUrl(array $params = []): string{
		return $this->getStudyLinkStatic($params);
	}
	/**
	 * @return StudyCard
	 */
	public function getOptionsListCard(): StudyCard{
		return $this->getStudyCard();
	}

	/**
	 * @return string
	 * Need getPostNameSlug because we have to override and use study UniqueIndexIdsSlug in QMCorrelations to avoid
	 *     duplicate posts
	 */
	public function getPostNameSlug(): string{
		return $this->getStudyId();
	}
	public function getKeyWords(): array{
		return $this->getStudyKeywords();
	}
	public function getAnalysisParameters(): ?array{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Study::FIELD_ANALYSIS_PARAMETERS] ?? null;
		} else{
			/** @var QMStudy $this */
			return $this->analysisParameters;
		}
	}
	public function setAnalysisParameters(array $analysisParameters): void{
		$this->setAttribute(Study::FIELD_ANALYSIS_PARAMETERS, $analysisParameters);
	}
	public function setCauseVariableId(int $causeVariableId): void{
		$this->setAttribute(Study::FIELD_CAUSE_VARIABLE_ID, $causeVariableId);
	}
	public function getCommentStatus(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Study::FIELD_COMMENT_STATUS] ?? null;
		} else{
			/** @var QMStudy $this */
			return $this->commentStatus;
		}
	}
	public function setCommentStatus(string $commentStatus): void{
		$this->setAttribute(Study::FIELD_COMMENT_STATUS, $commentStatus);
	}
	public function getDeletedAt(): ?string{
		return $this->attributes[Study::FIELD_DELETED_AT] ?? null;
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(Study::FIELD_DELETED_AT, $deletedAt);
	}
	public function setEffectVariableId(int $effectVariableId): void{
		$this->setAttribute(Study::FIELD_EFFECT_VARIABLE_ID, $effectVariableId);
	}
	public function getInternalErrorMessage(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Study::FIELD_INTERNAL_ERROR_MESSAGE] ?? null;
		} else{
			/** @var QMStudy $this */
			return $this->internalErrorMessage;
		}
	}
	public function setNewestDataAt(string $newestDataAt): void{
		$this->setAttribute(Study::FIELD_NEWEST_DATA_AT, $newestDataAt);
	}
	public function getReasonForAnalysis(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Study::FIELD_REASON_FOR_ANALYSIS] ?? null;
		} else{
			/** @var QMStudy $this */
			return $this->reasonForAnalysis;
		}
	}
	public function setStudyStatus(string $studyStatus): void{
		$this->setAttribute(Study::FIELD_STUDY_STATUS, $studyStatus);
	}
	public function getUserErrorMessage(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Study::FIELD_USER_ERROR_MESSAGE] ?? null;
		} else{
			/** @var QMStudy $this */
			return $this->userErrorMessage;
		}
	}
	public function setUserId(int $userId): void{
		$this->setAttribute(Study::FIELD_USER_ID, $userId);
	}
	public function getUserTitle(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Study::FIELD_USER_TITLE] ?? null;
		} else{
			/** @var QMStudy $this */
			return $this->userTitle;
		}
	}
	public function setUserTitle(string $userTitle): void{
		$this->setAttribute(Study::FIELD_USER_TITLE, $userTitle);
	}
	/**
	 * @return array
	 * @throws NoIdException
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new StudyCauseVariableButton($this),
			new StudyEffectVariableButton($this),
		];
	}
	public function getAvatar(): string{
		return Study::DEFAULT_IMAGE;
	}
	public static function getS3Bucket(): string{ return S3Private::getBucketName(); }
	public function getNameAttribute(): string{
		return $this->getStudyTitle();
	}
	public function getSortingScore(): float{
		if($c = $this->getHasCorrelationCoefficientIfSet()){
			return $c->getSortingScore();
		}
		return 1;
	}
	/**
	 * @return void
	 */
	public function sendStudyPublishedNotification(): void{
		$this->getUser()->notify(new StudyPublished($this->l()));
	}
	public function getSideMenus(): array{ return $this->getStudySideMenus(); }
	public function getActionsMenu(): ?QMMenu{ return $this->getStudyActionsMenu(); }
	/**
	 * @return StudyHtml
	 */
	public function getStudyHtml(): StudyHtml{
		return $this->getOrSetQMStudy()->getStudyHtml();
	}
	/**
	 * @return QMStudy|HasCauseAndEffect
	 */
	public function getOrSetQMStudy(): QMStudy{
		return $this->getDBModel();
	}
	public function votes(): HasMany{
		return $this->hasMany(Vote::class, [self::FIELD_CAUSE_VARIABLE_ID, self::FIELD_EFFECT_VARIABLE_ID],
			[self::FIELD_CAUSE_VARIABLE_ID, self::FIELD_EFFECT_VARIABLE_ID]);
	}
	/**
	 * @param Request $request
	 * @return QueryBuilder
	 */
	public static function index(Request $request): \Illuminate\Database\Eloquent\Collection{
		$models = parent::index($request);
		if(!$models->count()){
			$models = self::whereIsPublic(true)->get();
		}
		return $models;
	}
	/**
	 * @param User|int|null $reader
	 * @return bool
	 */
	public function canReadMe($reader = null): bool{
		if($this->type === StudyTypeProperty::TYPE_POPULATION){return true;}
		return parent::canReadMe($reader);
	}
}
