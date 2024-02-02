<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\States\ConfigurationStateButton;
use App\Models\Base\BaseCollaborator;
use App\Properties\Collaborator\CollaboratorTypeProperty;
use App\Slim\Middleware\QMAuth;
use App\Traits\HasModel\HasUser;
use App\UI\FontAwesome;
use App\Utils\UrlHelper;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\Collaborator
 * @property int $id
 * @property int $user_id
 * @property int $app_id
 * @property string $type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $deleted_at
 * @property string|null $client_id
 * @property-read Application $application
 * @property-read User $user
 * @method static Builder|Collaborator newModelQuery()
 * @method static Builder|Collaborator newQuery()
 * @method static Builder|Collaborator query()
 * @method static Builder|Collaborator whereAppId($value)
 * @method static Builder|Collaborator whereClientId($value)
 * @method static Builder|Collaborator whereCreatedAt($value)
 * @method static Builder|Collaborator whereDeletedAt($value)
 * @method static Builder|Collaborator whereId($value)
 * @method static Builder|Collaborator whereType($value)
 * @method static Builder|Collaborator whereUpdatedAt($value)
 * @method static Builder|Collaborator whereUserId($value)
 * @mixin Eloquent
 * @property-read OAClient|null $oa_client
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 */
class Collaborator extends BaseCollaborator {
    use HasFactory;

	use HasUser;
	public const CLASS_DESCRIPTION = "Collaborators are allowed to modify settings for applications created at https://builder.quantimo.do. ";
	const CLASS_CATEGORY = Application::CLASS_CATEGORY;
	public const FONT_AWESOME = FontAwesome::PEOPLE_CARRY_SOLID;
	protected $with = [//'user', 'application' // Too complicated and redundant data. Just get relations directly
	];
	protected array $rules = [
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_APP_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_TYPE => 'required',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
	];
	/**
	 * @param string $clientId
	 */
	public static function authCheck(string $clientId){
		if(!self::userIsCollaboratorOrAdmin($clientId)){
			QMAuth::throwUnauthorizedException("You are not a collaborator of this application.  Please contact " .
				self::getOwnerEmail($clientId) . "  and ask them to add your email at " .
				UrlHelper::getBuilderUrl($clientId));
		}
	}
	/**
	 * @param string $clientId
	 * @return User
	 */
	public static function getOwner(string $clientId): User {
		$owner =
			User::query()->join(self::TABLE, self::TABLE . '.user_id', '=', User::TABLE . '.ID')
				->where(self::TABLE . '.type', CollaboratorTypeProperty::TYPE_OWNER)
				->where(self::TABLE . '.client_id', $clientId)
				->first();
		return $owner;
	}
	/**
	 * @param string $clientId
	 */
	public static function getOwnerEmail(string $clientId){
		self::getOwner($clientId)->user_email;
	}
	/**
	 * @param string $clientId
	 * @param bool $throwException
	 * @param User|null $user
	 * @return bool
	 */
	public static function userIsCollaboratorOrAdmin(string $clientId, bool $throwException = false, User $user = null): bool{
		if(!$user){$user = QMAuth::getUser();}
		if(!$user){
			if($throwException){QMAuth::throwUnauthorizedException("You are not a collaborator of this application!");}
			return false;
		}
		if($user->isAdmin()){
			return true;
		}
		$physicianClientId = $user->getPhysicianClientId();
		if($physicianClientId === $clientId){return true;}
		$userId = $user->id;
		$client = OAClient::findInMemoryOrDB($clientId);
		if($client->user_id === $userId){return true;}
		$qb = self::whereUserId($userId)->where(self::FIELD_CLIENT_ID, $clientId);
		if($qb->first()){return true;}
		if($throwException){
			QMAuth::throwUnauthorizedException("You are not a collaborator of this application!");
		}
		return false;
	}
	public function getTitleAttribute(): string{
        if(!$this->user){return static::CLASS_DESCRIPTION;}
		return $this->user->display_name . " - " . $this->application->app_display_name;
	}
	public function getImage(): string{
		if(!$this->user){
			return static::DEFAULT_IMAGE;
		}
		return $this->user->getImage();
	}
	public function getEditUrl(array $params = []): string{
		$params[self::FIELD_CLIENT_ID] = $this->client_id;
		return ConfigurationStateButton::make()->getUrl($params);
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
}/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */


