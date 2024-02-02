<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TrackingReminder;
use App\Properties\User\UserIdProperty;

class TrackingReminderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => UserIdProperty::USER_ID_TEST_USER,
            'client_id' => \App\Models\OAClient::fakeFromPropertyModels()->client_id,
            'variable_id' => $this->faker->randomDigitNotNull,
            'default_value' => $this->faker->randomDigitNotNull,
            'reminder_start_time' => $this->faker->word,
            'reminder_end_time' => $this->faker->word,
            'reminder_sound' => $this->faker->word,
            'reminder_frequency' => $this->faker->randomDigitNotNull,
            'pop_up' => $this->faker->word,
            'sms' => $this->faker->word,
            'email' => $this->faker->word,
            'notification_bar' => $this->faker->word,
            'last_tracked' => $this->faker->date('Y-m-d H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'start_tracking_date' => $this->faker->word,
            'stop_tracking_date' => $this->faker->word,
            'instructions' => $this->faker->text,
            'deleted_at' => null,
            'unit_id' => \App\Units\OneToFiveRatingUnit::ID,
            'image_url' => $this->faker->url,
            'user_variable_id' => $this->faker->randomDigitNotNull,
            'latest_tracking_reminder_notification_notify_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
