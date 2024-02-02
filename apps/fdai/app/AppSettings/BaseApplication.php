<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings;
use App\Buttons\Links\AboutUsButton;
use App\Buttons\States\StudyStateButton;
use App\DataSources\QMClient;
use App\DataSources\QMDataSource;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseScopeProperty;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Types\QMStr;
use App\UI\ImageHelper;
class BaseApplication extends QMDataSource {
    public $appDescription;
    public $appDisplayName;
    public $additionalSettings;
    public $appType;
    public $clientId;
    public $homepageUrl;
    public $iconUrl;
    public $outcomeVariableId;
    public $ownerAvatar;
    public $ownerDisplayName;
    public $ownerEmail;
    public $physician;
    public $predictorVariableId;
    public $scope;
    public $study;
    public $userId;
    public $scopeDescription;
    public const TABLE = AppSettings::TABLE;
    /**
     * BaseApplication constructor.
     * @param object $row
     */
    public function __construct($row = null){
        $this->dataSourceType = QMDataSource::TYPE_CLIENT_APP;
        parent::__construct($row);
        $this->setFallbackIconUrl();
        if($this->study){
            $this->homepageUrl = StudyStateButton::getStudyUrl(['studyClientId' => $this->clientId]);
        }
        $this->scopeDescription = BaseScopeProperty::getScopeDescriptionFromString($this->scope);
    }
    public function getNameAttribute(): string{
        return $this->name = $this->appDisplayName;
    }
    /**
     * @return AdditionalSettings
     */
    public function getAdditionalSettings(): AdditionalSettings {
        if($this->additionalSettings instanceof AdditionalSettings){
            return $this->additionalSettings;
        }
        return $this->additionalSettings = new AdditionalSettings($this);
    }
    /**
     * @param bool $aggregate
     * @return QMQB
     */
    public static function getBaseSelectQuery(bool $aggregate = false): QMQB{
        $qb = self::readonly();
        $db = ReadonlyDB::db();
        $qb->join(QMClient::TABLE, 'oa_clients.client_id', '=', 'applications.client_id');
        $qb->leftJoin(User::TABLE, User::TABLE.'.'. User::FIELD_ID, '=',
            AppSettings::TABLE.'.'.AppSettings::FIELD_USER_ID);
        $fields = self::getAppSettingsFields($aggregate);
        foreach($fields as $field){
            if($aggregate){
                if(stripos($field, 'MAX') === false){
                    $qb->columns[] = $db->raw("MAX($field) as ".QMStr::after('.', $field, $field));
                }else{
                    $qb->columns[] = $db->raw($field);
                }
            }else{
                $qb->columns[] = $field;
            }
        }
        return $qb;
    }
    /**
     * @param bool $aggregate
     * @return array
     */
    private static function getAppSettingsFields(bool $aggregate = false): array{
        $fields = [
            AppSettings::TABLE.".additional_settings",
            AppSettings::TABLE.".app_design",
            AppSettings::TABLE.".app_status",
            AppSettings::TABLE.".app_type",
            AppSettings::TABLE.".build_enabled",
            AppSettings::TABLE.".client_id",
            AppSettings::TABLE.".company_name",
            AppSettings::TABLE.".homepage_url",
            AppSettings::TABLE.".icon_url",
            AppSettings::TABLE.".long_description",
            AppSettings::TABLE.".splash_screen",
            AppSettings::TABLE.".text_logo",
            AppSettings::TABLE.".user_id",
            AppSettings::TABLE.'.'.AppSettings::FIELD_APP_DESCRIPTION,
            AppSettings::TABLE.'.'.AppSettings::FIELD_APP_DISPLAY_NAME,
            AppSettings::TABLE.'.'.AppSettings::FIELD_APP_TYPE,
            AppSettings::TABLE.'.'.AppSettings::FIELD_CREATED_AT,
            AppSettings::TABLE.'.'.AppSettings::FIELD_ICON_URL,
            AppSettings::TABLE.'.'.AppSettings::FIELD_OUTCOME_VARIABLE_ID,
            AppSettings::TABLE.'.'.AppSettings::FIELD_PHYSICIAN,
            AppSettings::TABLE.'.'.AppSettings::FIELD_PREDICTOR_VARIABLE_ID,
            AppSettings::TABLE.'.'.AppSettings::FIELD_STUDY,
            AppSettings::TABLE.'.'.AppSettings::FIELD_UPDATED_AT,
            QMClient::TABLE.".client_secret",
            QMClient::TABLE.".redirect_uri",
        ];
        if($aggregate){
            $fields[] = 'MAX('. User::TABLE.'.'. User::FIELD_AVATAR_IMAGE.') as ownerAvatar';
            $fields[] = 'MAX('. User::TABLE.'.'. User::FIELD_DISPLAY_NAME.') as ownerDisplayName';
            $fields[] = 'MAX('. User::TABLE.'.'. User::FIELD_USER_EMAIL.') as ownerEmail';
        }else{
            $fields[] = User::TABLE.'.'. User::FIELD_AVATAR_IMAGE.' as ownerAvatar';
            $fields[] = User::TABLE.'.'. User::FIELD_DISPLAY_NAME.' as ownerDisplayName';
            $fields[] = User::TABLE.'.'. User::FIELD_USER_EMAIL.' as ownerEmail';
        }
        return $fields;
    }
    private function setFallbackIconUrl(){
        $badIcon = empty($this->iconUrl) || strpos($this->iconUrl, 'http') === false;
        if($badIcon && isset($this->additionalSettings->appImages->appIcon)){
            $this->iconUrl = $this->additionalSettings->appImages->appIcon;
        }
        if(empty($this->iconUrl) || strpos($this->iconUrl, 'http') === false){
            $this->iconUrl = $this->ownerAvatar;
        }
        if(empty($this->iconUrl) || strpos($this->iconUrl, 'http') === false){
            $this->iconUrl = ImageHelper::getStudyPngUrl();
        }
    }
    /**
     * @param string $newClientId
     * @param bool $dryRun
     */
    public function updateClientId(string $newClientId, bool $dryRun = true){
        Writable::replaceEverywhere(QMClient::FIELD_CLIENT_ID, $this->getClientId(),
            $newClientId, $dryRun);
    }
    /**
     * @param string $clientId
     * @param string $reason
     * @return int
     */
    public static function softDeleteAllForClientId(string $clientId, string $reason): int {
        $qb = static::writable()->where(QMClient::FIELD_CLIENT_ID, $clientId);
        return $qb->softDelete([], $reason);
    }
    /**
     * @return string
     */
    public function getHomepageUrl(): string{
        if(!$this->homepageUrl){
            $this->homepageUrl = AboutUsButton::QM_INFO_URL;
        }
        return $this->homepageUrl;
    }
    /**
     * @return bool
     */
    public function isTestApp():bool{
        return BaseClientIdProperty::isTestClientId($this->clientId);
    }
}
