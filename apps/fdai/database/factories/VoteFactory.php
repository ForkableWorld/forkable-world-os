<?php



namespace Database\Factories;

use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Vote;
use App\Properties\User\UserIdProperty;

class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'client_id' => \App\Models\OAClient::fakeFromPropertyModels()->client_id,
            'user_id' => UserIdProperty::USER_ID_TEST_USER,
            'value' => 1,
//            'created_at' => $this->faker->date('Y-m-d H:i:s'),
//            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
//            'deleted_at' => null,
            'cause_variable_id' => BupropionSrCommonVariable::ID,
            'effect_variable_id' => OverallMoodCommonVariable::ID,
        ];
    }
}
