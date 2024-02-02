<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SentEmail;
use App\Properties\User\UserIdProperty;

class SentEmailFactory extends Factory
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
            'type' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null,
            'client_id' => \App\Models\OAClient::fakeFromPropertyModels()->client_id,
            'slug' => $this->faker->word,
            'response' => $this->faker->word,
            'content' => $this->faker->text,
            'wp_post_id' => null,
            'email_address' => $this->faker->word
        ];
    }
}
