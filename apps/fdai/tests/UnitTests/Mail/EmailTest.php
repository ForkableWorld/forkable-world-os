<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Mail;
use App\AppSettings\AppStatus\BuildStatus;
use App\Mail\EmailsDisabledException;
use App\Mail\TrackingReminderNotificationEmail;
use App\Models\Application;
use App\Models\Collaborator;
use App\Models\SentEmail;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\TestDB;
use Tests\QMBaseTestCase;
use Tests\UnitTestCase;
/**
 * @coversDefaultClass \App\Mail\QMMailable
 */
class EmailTest extends UnitTestCase
{
    /**
     * @covers \App\Mail\TrackingReminderNotificationEmail::sendToAll()
     *  Doesn't work yet because we don't have a notification generator
     */
    public function testTrackingReminderNotificationEmail(){
        SentEmail::deleteAll();
        $mailCount = TrackingReminderNotificationEmail::sendToAll();
        $this->assertCount(0, $mailCount, $mailCount['message'] ?? "");
    }
	/**
	 * @throws \App\Exceptions\ClientNotFoundException
	 * @covers \App\Mail\AndroidBuildEmail
	 */
	public function testAndroidBuildEmail(){
        $this->skipTest("TODO");
        $appSettings = Application::getClientAppSettings(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
        $this->assertEquals("OAuth Test Client", $appSettings->appDisplayName);
        foreach ($appSettings->appStatus->buildStatus as $key => $value){
            $appSettings->appStatus->buildStatus->$key = BuildStatus::STATUS_READY;
            $appSettings->appStatus->betaDownloadLinks->$key = "https://s3.com";
        }
        Application::updateApplication(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, $appSettings);
        $this->postEmailRequest([
            'emailType' => 'android-build-ready',
            'clientId' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ]);
    }
	/**
	 *@covers \App\Mail\FitbitEmail
	 */
	public function testFitbitEmail(){$this->postEmailRequest(['emailType' => 'fitbit']);}
	/**
	 *@covers \App\Mail\ChromeExtensionEmail
	 */
	public function testChromeEmail(){$this->postEmailRequest(['emailType' => 'chrome']);}
	/**
	 *@covers \App\Mail\TrackingReminderNotificationEmail::sendToAll()
	 */
	public function testCouponEmail(){
        $this->postEmailRequest(['emailType' => 'coupon-instructions']);
    }
	/**
	 *@covers \App\Http\Controllers\EmailController
	 */
	public function testPhysicianEmail(){
        $this->postEmailRequest(['emailType' => 'patient-authorization', 'doctorName' => 'mike',
            'doctorEmail' => 'test@quantimo.do', 'patientName' => 'Patient Name']);
    }
    /**
     * @param array $body
     * @covers \App\Http\Controllers\EmailController
     */
    public function postEmailRequest(array $body = []){
        $this->skipTest("TODO");
        SentEmail::deleteAll();
        $this->actingAsUserOne();
        $body['clientId'] = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
        $response = $this->postWithClientId('/api/v2/email', $body);
        $this->compareLastEmail();
    }
    /**
     * @param int $length
     * @return string
     */
    public static function generateRandomString(int $length = 10): string{
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {$randomString .= $characters[rand(0, $charactersLength - 1)];}
        QMUser::writable()->where('ID', 1)->update(['user_email' => $randomString . '@gmail.com']);
        return $randomString;
    }
    public function testUnsubscribedException(){
        //TestDB::importTestDatabase();
        $physicianEmail = 'test@quantimo.do';
        QMUser::writable()
            ->where(User::FIELD_USER_EMAIL, $physicianEmail)
            ->update([User::FIELD_UNSUBSCRIBED => true]);
        $postData = ['physician_email' => $physicianEmail, 'scopes' => 'readmeasurements'];
        $this->setAuthenticatedUser(2);
        QMBaseTestCase::setExpectedRequestException(EmailsDisabledException::class);
        $this->postApiV6('shares', $postData, 400);
    }
}
