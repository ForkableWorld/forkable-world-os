<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\WordPress;
use App\Logging\QMLog;
use App\Slim\Model\DBModel;
use App\Slim\Model\User\QMUser;
use Exception;
class WPUserApi extends DBModel {
	public const STUDIES_CLIENT_ID = 'studies';
	private $existing;
	private $qmUser;
	public $description;
	public $email;
	public $first_name;
	public $last_name;
	public $locale;
	public $meta;
	public $name;
	public $nickname; // The nickname exists to give you an option to set display name to something other than your username or real name.
	public $password;
	public $roles;
	public $slug;
	public $url;
	public $username;
	/**
	 * WPUserApi constructor.
	 * @param QMUser $QMUser
	 */
	public function __construct(QMUser $QMUser){
		$this->setQmUser($QMUser);
		$this->populateFieldsByArrayOrObject($QMUser);
		//$this->meta = $QMUser->getAllUserMetaData();  // This could be dangerous if WP is hacked
		$this->username = $QMUser->getLoginName();
		$this->name = $QMUser->getDisplayNameAttribute();
		// Display name is selectable between the user’s username, first name, last name, first/last, or last/first, or nickname.
		// The nickname exists to give you an option to set display name to something other than your username or real name.
		$this->nickname = $QMUser->getLoginName();
		$this->slug = $QMUser->getUrlSafeNiceName();
		$this->roles = ["author"];
		$this->password = $QMUser->getEncryptedPasswordHash();
		$this->first_name = $QMUser->getFirstName();
		$this->last_name = $QMUser->getLastName();
		$this->url = $QMUser->userUrl;
		$this->setId($QMUser->getClientUserId(self::STUDIES_CLIENT_ID));
		$this->updateOrCreate();
	}
	/**
	 * @return mixed
	 */
	public function updateOrCreate(){
		try {
			$existing = $this->getExisting();
			$body = json_decode(json_encode($this));
			if($existing){
				$changed = false;
				foreach($existing as $key => $value){
					if(property_exists($this, $key) && $this->$key !== $value){
						$changed = true;
					}
				}
				if($changed){
					$response = QMWordPressApi::post('users/' . $this->getId(), $body);
					return $response;
				}
				return $existing;
			} else{
				$response = QMWordPressApi::post('users', $body);
				$this->existing = $response;
				$this->getQmUser()->setClientUserId(self::STUDIES_CLIENT_ID, $response->id);
				return $response;
			}
		} catch (Exception $e) {
			QMLog::error($e->getMessage(), []);
			return false;
		}
	}
	/**
	 * @return object|bool
	 */
	public function getExisting(){
		if($this->existing !== null){
			return $this->existing;
		}
		$id = $this->getQmUser()->getClientUserId(self::STUDIES_CLIENT_ID);
		$existing = false;
		if($id){
			$existing = QMWordPressApi::get('users/' . $id);
			$this->populateFieldsByArrayOrObject($existing);
		}
		return $this->existing = $existing;
	}
	/**
	 * @param QMUser $QMUser
	 * @return WPUserApi
	 */
	public static function getOrCreateWpUser(QMUser $QMUser): WPUserApi{
		$wpUser = new WPUserApi($QMUser);
		return $wpUser;
	}
	/**
	 * @return string
	 */
	public function getUsername(){
		return $this->username;
	}
	/**
	 * @return string
	 */
	public function getEmail(){
		return $this->email;
	}
	/**
	 * @return QMUser
	 */
	public function getQmUser(){
		return $this->qmUser;
	}
	/**
	 * @param QMUser $qmUser
	 */
	public function setQmUser(QMUser $qmUser){
		$this->qmUser = $qmUser;
	}
}
