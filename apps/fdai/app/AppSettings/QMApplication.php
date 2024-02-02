<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings;
use App\Models\Application;
use App\Storage\DB\Writable;
use App\Slim\Model\DBModel;
/**
 * @mixin Application
 */
class QMApplication extends DBModel {
    public const TABLE = 'applications';
    public const FIELD_ADDITIONAL_SETTINGS = 'additional_settings';
    public const FIELD_ADDRESS = 'address';
    public const FIELD_APP_DESCRIPTION = 'app_description';
    public const FIELD_APP_DESIGN = 'app_design';
    public const FIELD_APP_DISPLAY_NAME = 'app_display_name';
    public const FIELD_APP_STATUS = 'app_status';
    public const FIELD_APP_TYPE = 'app_type';
    public const FIELD_BILLING_ENABLED = 'billing_enabled';
    public const FIELD_BUILD_ENABLED = 'build_enabled';
    public const FIELD_CITY = 'city';
    public const FIELD_CLIENT_ID = 'client_id';
    public const FIELD_COMPANY_NAME = 'company_name';
    public const FIELD_COUNTRY = 'country';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_DELETED_AT = 'deleted_at';
    public const FIELD_ENABLED = 'enabled';
    public const FIELD_EXCEEDING_CALL_CHARGE = 'exceeding_call_charge';
    public const FIELD_EXCEEDING_CALL_COUNT = 'exceeding_call_count';
    public const FIELD_HOMEPAGE_URL = 'homepage_url';
    public const FIELD_ICON_URL = 'icon_url';
    public const FIELD_ID = 'id';
    public const FIELD_LAST_FOUR = 'last_four';
    public const FIELD_LONG_DESCRIPTION = 'long_description';
    public const FIELD_ORGANIZATION_ID = 'organization_id';
    public const FIELD_OUTCOME_VARIABLE_ID = 'outcome_variable_id';
    public const FIELD_PHYSICIAN = 'physician';
    public const FIELD_PLAN_ID = 'plan_id';
    public const FIELD_PREDICTOR_VARIABLE_ID = 'predictor_variable_id';
    public const FIELD_SPLASH_SCREEN = 'splash_screen';
    public const FIELD_STATE = 'state';
    public const FIELD_STATUS = 'status';
    public const FIELD_STRIPE_ACTIVE = 'stripe_active';
    public const FIELD_STRIPE_ID = 'stripe_id';
    public const FIELD_STRIPE_PLAN = 'stripe_plan';
    public const FIELD_STRIPE_SUBSCRIPTION = 'stripe_subscription';
    public const FIELD_STUDY = 'study';
    public const FIELD_SUBSCRIPTION_ENDS_AT = 'subscription_ends_at';
    public const FIELD_TEXT_LOGO = 'text_logo';
    public const FIELD_TRIAL_ENDS_AT = 'trial_ends_at';
    public const FIELD_UPDATED_AT = 'updated_at';
    public const FIELD_USER_ID = 'user_id';
    public const FIELD_ZIP = 'zip';
    /**
     * AppStatus constructor.
     */
    public function __construct(){
    }
    /**
     * @param string $clientId
     * @return int
     */
    public static function softDeleteByClientId($clientId){
        return self::writable()
            ->where(self::FIELD_CLIENT_ID, $clientId)
            ->update([Writable::FIELD_DELETED_AT => date('Y-m-d H:i:s')]);
    }
}
