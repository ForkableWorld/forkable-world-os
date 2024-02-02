<?php



namespace Database\Factories;

use App\Properties\DeviceToken\DeviceTokenPlatformProperty;
use App\Utils\IPHelper;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DeviceToken;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use Tests\SlimTests\Controllers\DeviceTokensTest;

class DeviceTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
//            'created_at' => $this->faker->date('Y-m-d H:i:s'),
//            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null,
            DeviceToken::FIELD_DEVICE_TOKEN => DeviceTokensTest::VALID_QM_ANDROID_DEVICE_TOKEN,
            'user_id' => UserIdProperty::USER_ID_TEST_USER,
            'number_of_waiting_tracking_reminder_notifications' => 1,
            'last_notified_at' => $this->faker->date('Y-m-d H:i:s'),
            'platform' => DeviceTokenPlatformProperty::PLATFORM_IOS,
            'number_of_new_tracking_reminder_notifications' => 1,
            'number_of_notifications_last_sent' => 1,
            'error_message' => null,
            'last_checked_at' => null,
            'received_at' => null,
            'server_ip' => IPHelper::IP_MIKE,
            'server_hostname' => $this->faker->word,
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ];
    }
}
