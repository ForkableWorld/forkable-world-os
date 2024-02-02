<?php


namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OAClient;
use App\Models\TrackingReminderNotification;
use App\Models\User;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;

class TrackingReminderNotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::testUser();
        $uv = $user->getOrCreateUserVariable(OverallMoodCommonVariable::ID);
        $tr = $uv->firstOrCreateTrackingReminder();
        return [
            'tracking_reminder_id' => $tr->getId(),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null,
            'user_id' => $tr->getUserId(),
            'notified_at' => $this->faker->date('Y-m-d H:i:s'),
            'received_at' => $this->faker->date('Y-m-d H:i:s'),
            'client_id' => OAClient::fakeFromPropertyModels()->client_id,
            'variable_id' => $tr->getVariableIdAttribute(),
            'notify_at' => $this->faker->date('Y-m-d H:i:s'),
            'user_variable_id' =>  $tr->getUserVariableId()
        ];
    }
}
