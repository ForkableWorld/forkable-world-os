<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UserTag;
use App\Properties\User\UserIdProperty;

class UserTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tagged_variable_id' => $this->faker->randomDigitNotNull,
            'tag_variable_id' => $this->faker->randomDigitNotNull,
            'conversion_factor' => $this->faker->randomDigitNotNull,
            'user_id' => UserIdProperty::USER_ID_TEST_USER,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'client_id' => \App\Models\OAClient::fakeFromPropertyModels()->client_id,
            'deleted_at' => null
        ];
    }
}
