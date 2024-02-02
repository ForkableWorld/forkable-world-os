<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Models;
use App\Models\User;
use Tests\UnitTestCase;

class UserUnitTest extends UnitTestCase
{
    /**
     * Test "User"
     */
    public function testUserHtml(){
        $this->compareHtmlFragment('principal-investigator', User::mike()->getPrincipalInvestigatorProfileHtml());
        $this->compareHtmlFragment('bio', User::mike()->getBioHtml());
    }
    /**
     * Test "User"
     */
    public function TODO_testUser()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "access_token"
     */
    public function TODO_testPropertyAccessToken()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "access_token_expires"
     */
    public function TODO_testPropertyAccessTokenExpires()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "access_token_expires_at_milliseconds"
     */
    public function TODO_testPropertyAccessTokenExpiresAtMilliseconds()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "administrator"
     */
    public function TODO_testPropertyAdministrator()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "authorized_clients"
     */
    public function TODO_testPropertyAuthorizedClients()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "avatar"
     */
    public function TODO_testPropertyAvatar()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "avatar_image"
     */
    public function TODO_testPropertyAvatarImage()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "capabilities"
     */
    public function TODO_testPropertyCapabilities()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "card"
     */
    public function TODO_testPropertyCard()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "client_id"
     */
    public function TODO_testPropertyClientId()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "client_user_id"
     */
    public function TODO_testPropertyClientUserId()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "combine_notifications"
     */
    public function TODO_testPropertyCombineNotifications()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "created_at"
     */
    public function TODO_testPropertyCreatedAt()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "description"
     */
    public function TODO_testPropertyDescription()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "display_name"
     */
    public function TODO_testPropertyDisplayName()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "earliest_reminder_time"
     */
    public function TODO_testPropertyEarliestReminderTime()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "email"
     */
    public function TODO_testPropertyEmail()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "first_name"
     */
    public function TODO_testPropertyFirstName()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "get_preview_builds"
     */
    public function TODO_testPropertyGetPreviewBuilds()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "has_android_app"
     */
    public function TODO_testPropertyHasAndroidApp()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "has_chrome_extension"
     */
    public function TODO_testPropertyHasChromeExtension()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "has_ios_app"
     */
    public function TODO_testPropertyHasIosApp()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "id"
     */
    public function TODO_testPropertyId()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "last_active"
     */
    public function TODO_testPropertyLastActive()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "last_four"
     */
    public function TODO_testPropertyLastFour()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "last_name"
     */
    public function TODO_testPropertyLastName()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "last_sms_tracking_reminder_notification_id"
     */
    public function TODO_testPropertyLastSmsTrackingReminderNotificationId()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "latest_reminder_time"
     */
    public function TODO_testPropertyLatestReminderTime()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "login_name"
     */
    public function TODO_testPropertyLoginName()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "password"
     */
    public function TODO_testPropertyPassword()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "phone_number"
     */
    public function TODO_testPropertyPhoneNumber()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "phone_verification_code"
     */
    public function TODO_testPropertyPhoneVerificationCode()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "primary_outcome_variable_id"
     */
    public function TODO_testPropertyPrimaryOutcomeVariableId()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "primary_outcome_variable_name"
     */
    public function TODO_testPropertyPrimaryOutcomeVariableName()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "push_notifications_enabled"
     */
    public function TODO_testPropertyPushNotificationsEnabled()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "refresh_token"
     */
    public function TODO_testPropertyRefreshToken()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "roles"
     */
    public function TODO_testPropertyRoles()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "send_predictor_emails"
     */
    public function TODO_testPropertySendPredictorEmails()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "send_reminder_notification_emails"
     */
    public function TODO_testPropertySendReminderNotificationEmails()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "share_all_data"
     */
    public function TODO_testPropertyShareAllData()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "sms_notifications_enabled"
     */
    public function TODO_testPropertySmsNotificationsEnabled()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "stripe_active"
     */
    public function TODO_testPropertyStripeActive()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "stripe_id"
     */
    public function TODO_testPropertyStripeId()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "stripe_plan"
     */
    public function TODO_testPropertyStripePlan()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "stripe_subscription"
     */
    public function TODO_testPropertyStripeSubscription()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "subscription_ends_at"
     */
    public function TODO_testPropertySubscriptionEndsAt()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "subscription_provider"
     */
    public function TODO_testPropertySubscriptionProvider()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "time_zone_offset"
     */
    public function TODO_testPropertyTimeZoneOffset()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "track_location"
     */
    public function TODO_testPropertyTrackLocation()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "updated_at"
     */
    public function TODO_testPropertyUpdatedAt()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "user_registered"
     */
    public function TODO_testPropertyUserRegistered()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
    /**
     * Test attribute "user_url"
     */
    public function TODO_testPropertyUserUrl()
    {
        $this->skipTest("TODO: Implement ".__FUNCTION__);
    }
}
