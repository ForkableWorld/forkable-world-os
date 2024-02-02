<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Exceptions\AccessTokenExpiredException;
use App\Exceptions\InsufficientScopeException;
use App\Slim\Model\Auth\OAuth2Server;
use App\Utils\AppMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseOAAccessToken;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\DBModel;
use App\Slim\Model\User\QMUser;
use App\Traits\HasDBModel;
use App\Traits\HasModel\HasUser;
use App\Traits\ModelTraits\OAAccessTokenTrait;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * App\Models\OAAccessToken
 * @property string $access_token
 * @property string $client_id
 * @property string|null $user_id
 * @property string|null $expires
 * @property string|null $scope
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property string|null $deleted_at
 * @property-read Application $application
 * @method static Builder|OAAccessToken newModelQuery()
 * @method static Builder|OAAccessToken newQuery()
 * @method static Builder|OAAccessToken query()
 * @method static Builder|OAAccessToken whereAccessToken($value)
 * @method static Builder|OAAccessToken whereClientId($value)
 * @method static Builder|OAAccessToken whereCreatedAt($value)
 * @method static Builder|OAAccessToken whereDeletedAt($value)
 * @method static Builder|OAAccessToken whereExpires($value)
 * @method static Builder|OAAccessToken whereScope($value)
 * @method static Builder|OAAccessToken whereUpdatedAt($value)
 * @method static Builder|OAAccessToken whereUserId($value)
 * @mixin Eloquent
 * @property-read OAClient $oa_client
 * @property-read User $user
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient $client
 */
class OAAccessToken extends BaseOAAccessToken {
    use HasFactory;

	use OAAccessTokenTrait;
	use HasUser, HasDBModel;
	use SearchesRelations;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = OAAccessToken::FIELD_ID;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		OAAccessToken::FIELD_CLIENT_ID,
		OAAccessToken::FIELD_ACCESS_TOKEN,
	];
    public $hidden = [];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'user' => [User::FIELD_DISPLAY_NAME],
	];
	//public $with = ['user'];
	public static $group = OAAccessToken::CLASS_CATEGORY;
	public static function getSlimClass(): string{ return QMAccessToken::class; }
	public const CLASS_DESCRIPTION = "Access tokens are the thing that applications use to make API requests on behalf of a user.  ";
	public const FIELD_ID = self::FIELD_ACCESS_TOKEN;
	public const FONT_AWESOME = FontAwesome::KEY_SOLID;
	public const DEFAULT_IMAGE = ImageUrls::ESSENTIAL_COLLECTION_KEY;
	public static function getUniqueIndexColumns(): array{
		return [self::FIELD_ACCESS_TOKEN];
	}
	const CLASS_CATEGORY = "Authentication";
	public $fillable = ['*'];
	protected $primaryKey = 'access_token';
    protected $keyType = 'string';
	/**
	 * @var bool Indicates if the IDs are auto-incrementing.
	 */
	public $incrementing = false;
	/**
	 * Get the displayable label of the resource.
	 * @return string
	 */
	public static function label(): string{
		return "Access Tokens";
	}
	public function __construct(array $attributes = []){ 
		parent::__construct($attributes); 
	}
	/**
	 * @param string|null $clientId
	 * @param int $limit
	 * @return OAAccessToken[]|Collection
	 */
	public static function getApplicationUserAccessTokens(string $clientId = null, int $limit = 100){
		$tokens = OAAccessToken::whereClientId($clientId)->whereNotNull('user_id')
			->where('expires', '>', Carbon::today()->toDateString())->limit($limit)->groupBy('user_id')
			->orderBy('expires', 'desc')->get();
		return $tokens;
	}

    public static function whereValid()
    {
        return static::where('expires', '>', Carbon::now());
    }

    /**
	 * @return HasOne|Application
	 */
	public function application(){
		return $this->hasOne(Application::class, 'client_id', 'client_id');
	}

	/**
	 * @param string $accessToken
	 * @return static|null
	 */
	public static function getByAccessToken(string $accessToken): ?self{
		return self::whereAccessToken($accessToken)->first();
	}
	/**
	 * @return QMAccessToken
	 */
	public function getDBModel(): DBModel{
		$t = new QMAccessToken();
		foreach($this->attributes as $key => $value){
			$camel = QMStr::camelize($key);
			$t->setAttribute($camel, $value);
		}
		return $t;
	}
	public function expire(){
		$this->expires = db_date(time()-1);
		$this->save();
	}
	/**
	 * @param array $attributes
	 * @return OAAccessToken
	 */
	public static function create(array $attributes = []): OAAccessToken{
		$t = parent::create($attributes);
		$t->addToMemory();
		return $t;
	}
	/**
	 * @return Collection|static[]
	 */
	public static function getPhysicianTokens(): Collection{
		return static::wherePhysicianToken()->get();
	}
	public static function wherePhysicianToken(): Builder{
		$needle = BaseClientIdProperty::PHYSICIAN_CLIENT_ID_PREFIX;
		return OAAccessToken::where(OAAccessToken::FIELD_CLIENT_ID, \App\Storage\DB\ReadonlyDB::like(), "$needle%");
	}
	/**
	 * @return Collection|string[]
	 */
	public static function getPhysicianClientIds(): Collection{
		return static::wherePhysicianToken()->groupBy([self::FIELD_CLIENT_ID])->pluck(self::FIELD_CLIENT_ID);
	}
	/**
	 * @return Collection|OAClient[]
	 */
	public static function getPhysicianClients(): Collection{
		$clientIds = static::getPhysicianClientIds();
		$clients = OAClient::whereIn(OAClient::FIELD_CLIENT_ID, $clientIds->all())->get();
		return $clients;
	}
	public static function getPhysicianUserIds(): array{
		$clientIds = static::getPhysicianClients();
		$userIds =
			OAClient::whereIn(OAClient::FIELD_CLIENT_ID, $clientIds->all())
                ->pluck(OAClient::FIELD_USER_ID)
                ->all();
		return array_unique($userIds);
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	public function getNameAttribute(): string{
		return $this->getClientId() . " Token For " . $this->getUserLoginName();
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		return QMAuth::isAdmin();
	}
	public function getQMUser(): QMUser{
		$user = $this->getUser()->getQMUser();
		$user->setAccessToken($this);
		return $user;
	}
    /**
     * @param array $attributes
     * @return static
     * @throws \App\Exceptions\ModelValidationException
     */
    public static function findInMemoryDBOrCreate(array $attributes = []): OAAccessToken{
        if($t = static::findInMemoryWhere($attributes)){return $t;}
        $t = static::where($attributes)
            ->where(self::FIELD_EXPIRES, '>', db_date(time()))
            ->orderByDesc(self::FIELD_EXPIRES)
            ->first();
        if($t){return $t;}
        $t = new static();
        $t->forceFill($attributes);
        $t->access_token = Str::random(40);
        $t->expires = now()->addDays(60)->unix();
        $t->save();
        return $t;
    }
	/**
	 * @param string|null $required_scope
	 * @throws AccessTokenExpiredException
	 * @throws InsufficientScopeException
	 */
	public function validateScopeAndExpiration(string $required_scope = null): void {
		if($this->isExpired()){
			$message = "Access token expired at ".$this->getExpires();
			if(AppMode::isTestingOrStaging()){
				$message .= $this->print();
			}
			throw new AccessTokenExpiredException($message);
		}
		if($required_scope){
			OAuth2Server::checkScope($required_scope, $this->scope);
		}
	}
	/**
	 * @return bool
	 */
	public function isExpired(): bool{
		$expires = $this->expires;
		return time_or_exception($expires) < time();
	}
	public static function firstOrCreate(array $attributes, array $values = []){
		return parent::firstOrCreate($attributes, $values); 
	}
	public function getFillable(): array{
		return self::getColumns(); 
	}
}
