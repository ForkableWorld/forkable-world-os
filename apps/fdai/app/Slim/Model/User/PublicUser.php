<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\User;
use App\AppSettings\AppSettings;
use App\DataSources\Connectors\FacebookConnector;
use App\DataSources\QMConnector;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\NoEmailAddressException;
use App\Exceptions\NoGeoDataException;
use App\Logging\ConsoleLog;
use App\Models\Connection;
use App\Models\User;
use App\Properties\Connection\ConnectionConnectStatusProperty;
use App\Properties\User\UserUserEmailProperty;
use App\Slim\Model\DBModel;
use App\Slim\Model\WordPress\QMWordPressApi;
use App\Traits\HasButton;

use App\Traits\HasPatients;
use App\Types\QMStr;
use App\UI\ImageHelper;
use GenderDetector\GenderDetector;
/**
 * @mixin User
 */
class PublicUser extends DBModel {
	use HasButton, HasPatients;
	public const FIELD_AVATAR_IMAGE   = 'avatar_image';
	public const FIELD_CREATED_AT     = 'created_at';
	public const FIELD_DISPLAY_NAME   = 'display_name';
	public const FIELD_FIRST_NAME     = 'first_name';
	public const FIELD_ID             = 'ID';
	public const FIELD_LAST_NAME      = 'last_name';
	public const FIELD_UPDATED_AT     = 'updated_at';
	public const FIELD_USER_EMAIL     = 'user_email';
	public const FIELD_USER_LOGIN     = 'user_login';
	public const FIELD_USER_NICENAME  = 'user_nicename';
	public const FIELD_USER_URL       = 'user_url';
	public const TABLE                = 'wp_users';
	const        DEFAULT_AVATAR_IMAGE = "http://4.bp.blogspot.com/-SRSVCXNxbAc/UrbxxXd06YI/AAAAAAAAFl4/332qncR9pD4/s1600/default-avatar.jpg";
	protected $profileHtml;
	protected $loginName;
	protected $patients;
	protected $connections;
	protected $connectors;
	protected $userMetaData; // Can't just use metaData because Mongo won't support it. Can't be public because there are secret keys and keys can have .'s which prevents saving to mongo
	public $avatarImage;
	public $avatar;
	public $createdAt;
	public $displayName;
	public $email;
	public $firstName;
	public $lastName;
	public $tagLine;
	public $unsubscribed;
	public $updatedAt;
	public $userUrl;
	public const LARAVEL_CLASS                      = User::class;
	public const DB_FIELD_NAME_TO_PROPERTY_NAME_MAP = [
		self::FIELD_ID => 'id',
		self::FIELD_USER_EMAIL => 'email',
	];
	/**
	 * PublicUser constructor.
	 * @param \App\Models\User|null $l
	 * @param bool $addFallbackAvatar
	 */
	public function __construct(User $l = null, bool $addFallbackAvatar = false){
		if(!$l){return;}
		$this->populateByLaravelModel($l);
		if($addFallbackAvatar && !isset($this->avatarImage)){$this->getAvatar();}
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return void
	 */
	public function populateFieldsByArrayOrObject(array|object $arrayOrObject): void{
		parent::populateFieldsByArrayOrObject($arrayOrObject);
		$this->populateDefaultFields();
	}
	public function populateDefaultFields(){
		$this->getDisplayNameAttribute();
	}
	/**
	 * @param string $displayName
	 */
	public function setDisplayName(string $displayName): void{
		$this->displayName = QMStr::titleCaseSlow($displayName);
	}
	public function validate(): void {
		if(empty($this->displayName)){
			le("No display name on " . \App\Logging\QMLog::print_r($this, true));
		}
	}
	/**
	 * @param int $id
	 * @return bool|PublicUser
	 */
	public static function find($id): ?DBModel{
		return new PublicUser(User::findInMemoryOrDB($id));
	}
	/**
	 * @return bool
	 */
	public function hasNonGravatarOrNonDefaultAvatar(): bool{
		$img = $this->getAvatar();
		return $img && stripos($img, 'gravatar') === false && $img !== self::DEFAULT_AVATAR_IMAGE;
	}
	/**
	 * @return string
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 */
	public function getEmail(): string{
		$email = UserUserEmailProperty::pluck($this);
		if(empty($email)){
			throw new NoEmailAddressException($this);
		}
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			throw new InvalidEmailException("Invalid email address: $email");
		}
		return $this->email;
	}
	/**
	 * @return string
	 */
	public function getAvatar(): string{
		$img = $this->avatarImage ?? $this->avatar;
		if($img){
			$img = QMStr::before("?sz", $img,
                $img); // Remove size param from google images to avoid pixelation
			if(strpos($img, '.jpg') !== false){
				return $this->avatar = $this->avatarImage = $img;
			}
			if(strpos($this->avatarImage, '.png') !== false){
				return $this->avatar = $this->avatarImage = $img;
			}
		}
		if(isset($this->userUrl)){
			$img = FacebookConnector::getAvatarFromFacebookUrl($this->userUrl);
		}
		//self::getFallbackUserAvatarFromGoogle($this); //Can't do this because it's too slow if we have lots of users
		if(!$img){
			try {
				$img = ImageHelper::generateGravatarImageOrDefault($this->getEmail());
			} catch (NoEmailAddressException | InvalidEmailException $e) {
				$img = self::DEFAULT_AVATAR_IMAGE;
			}
		}
		return $this->avatarImage = $this->avatar = $img;
	}
	/**
	 * @return string
	 */
	public function getDisplayNameAttribute(): string{
		if(!$this->displayName){
			if($this->firstName && $this->lastName){
				$this->setDisplayName($this->firstName . " " . $this->lastName);
			} elseif($this->loginName){
				$this->setDisplayName($this->loginName);
			} else{
				$this->setDisplayName("Anonymous Aardvark");
			}
		}
		return $this->displayName;
	}
	/**
	 * @return string
	 */
	public function getUserUrl(): ?string{
		return $this->userUrl;
	}
	/**
	 * @return string
	 */
	public function getPrincipalInvestigatorProfileHtml(): string{
		if($this->profileHtml){
			return $this->profileHtml;
		}
		//$html = HtmlHelper::renderBlade('principal-investigator-profile', ['publicUser' => $this]);
		$html = $this->getBioHtml();
		return $this->profileHtml = $html;
	}
	/**
	 * @return string
	 */
	public function getLoginName(): string{
		if(empty($this->loginName)){
			le("No LoginName!");
		}
		return $this->loginName;
	}
	/**
	 * @return bool
	 */
	public function unsubscribedFromEmails(): bool{
		return (bool)$this->unsubscribed;
	}
	/**
	 * @return string|null
	 */
	public function getIdWithNames(): ?string{
		return $this->getUrlSafeNiceName();
	}
	public function getBuddyPressProfileUrl(): string{
		$nicename = $this->getUrlSafeNiceName();
		$base = QMWordPressApi::getSiteUrl();
		return "$base/members/$nicename/profile/";
	}
	public function getPostArchiveButton(): string{
		$button = $this->getButton();
		$button->setTextAndTitle("Studies");
		$button->setUrl($this->getAuthorPostArchiveUrl());
		return $button->getRectangleWPButton();
	}
	/**
	 * @return string
	 */
	public function getImage(): string{
		return $this->getAvatar();
	}
	public function getUserId(): ?int{
		return $this->getId();
	}
	public function getAnonymousDescription(): string{
		return "Adult " . ucfirst($this->getGender());
	}
	/**
	 * @return string
	 */
	public function getGender(): ?string{
		if(!$this->gender){
			$name = $this->getFirstName();
			if(!empty($name)){
				$genderDetector = new GenderDetector();
				$gender = $genderDetector->detect($this->getFirstName());
				if(stripos($gender, 'male') !== false){
					return $this->gender = 'male';
				}
				if(stripos($gender, 'female') !== false){
					return $this->gender = 'female';
				}
			}
		}
		return $this->gender;
	}
	/**
	 * @return string
	 */
	public function getLastName(): ?string{
		return $this->lastName;
	}
	/**
	 * @return string
	 */
	public function getFirstName(): ?string{
		return $this->firstName;
	}
	public function getDriftIdentifyScript(): string{
		$email = $this->email;
		$name = $this->getLoginName();
		$role = ($this->isAdmin()) ? "administrator" : "author";
		return "
           <!-- Start Identify call for Drift -->
            <script>
            drift.identify(\"f436ad370b264b9f9f0b6a551c53364b\", { email: \"$email\", name: \"$name\", userRole: \"$role\" });
            </script>
            <!-- End Identify call for Drift -->
        ";
	}
	public static function getPublicUserColumns(bool $prefixWithTable): array{
		$fields = [
			self::FIELD_AVATAR_IMAGE,
			self::FIELD_CREATED_AT,
			self::FIELD_DISPLAY_NAME,
			self::FIELD_FIRST_NAME,
			self::FIELD_ID,
			self::FIELD_LAST_NAME,
			self::FIELD_UPDATED_AT,
			self::FIELD_USER_EMAIL,
			self::FIELD_USER_LOGIN,
			self::FIELD_USER_NICENAME,
			self::FIELD_USER_URL,
		];
		if($prefixWithTable){
			foreach($fields as $key => $column){
				$fields[$key] = static::TABLE . '.' . $column;
			}
		}
		return $fields;
	}
	public function getFontAwesome(): string{
		return User::FONT_AWESOME;
	}
	/**
	 * @return QMConnector[]
	 */
	public function getQMConnectors(): array{
		//return $this->setQMConnectors();
		return $this->connectors ?: $this->setQMConnectors();
	}
	/**
	 * @return QMConnector[]
	 */
	public function setQMConnectors(): array{
		$connectors = QMConnector::getAnonymousConnectors();
		$connections = $this->getConnections();
		$keep = [];
		$user = $this->getUser();
		foreach($connectors as $c){
			$c = clone $c;
			if(!$c->availableOutsideUS){
                try {
                    if($user->isOutsideUS()){continue;}
                } catch (NoGeoDataException $e) {
                    continue;
                }
			}
			if(!$c->enabled){
				continue;
			}
			$c->setUserId($this->getId());
			ConsoleLog::debug("Getting connection for " . $c->name . " for user " . $this->getId());
			//if($c->name === 'worldweatheronline'){debugger("wwo");}
			/** @var Connection $connection */
			$connection = $connections->firstWhere(Connection::FIELD_CONNECTOR_ID, $c->getId());
			if($connection){
				$c->addConnectionInfo($connection);
			} else{
				$c->connected = false;
				$c->connectStatus = ConnectionConnectStatusProperty::CONNECT_STATUS_DISCONNECTED;
			}
			$this->setDefaultButtons();
			if($c->connected === null){
				le($c);
			}
			$keep[$c->name] = $c;
		}
		return $this->connectors = $keep;
	}

	/**
	 * @param $instance
	 * @return void
	 */
	public function notify($instance){
		$u = $this->getUser();
		$u->notify($instance);
	}
	public function getUser(): User{
		return User::findInMemoryOrDB($this->getUserId());
	}
	public function getIsPublic(): ?bool{
		return $this->getUser()->is_public;
	}
	/**
	 * @return User
	 */
	public function l(): User{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return parent::l();
	}
}
