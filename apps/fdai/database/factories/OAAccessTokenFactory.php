<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OAAccessToken;
use App\Properties\User\UserIdProperty;

class OAAccessTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            OAAccessToken::FIELD_ACCESS_TOKEN => $this->faker->word,
            'client_id' => \App\Models\OAClient::fakeFromPropertyModels()->client_id,
            'user_id' => UserIdProperty::USER_ID_TEST_USER,
            'expires' => $this->faker->date('Y-m-d H:i:s'),
            'scope' => $this->faker->word,
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null
        ];
    }
}
