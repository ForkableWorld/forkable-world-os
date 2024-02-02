<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Subscription;
use App\Properties\User\UserIdProperty;

class SubscriptionFactory extends Factory
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
            'name' => $this->faker->word,
            'stripe_id' => $this->faker->randomDigitNotNull,
            'stripe_plan' => $this->faker->word,
            'quantity' => $this->faker->randomDigitNotNull,
            'trial_ends_at' => $this->faker->date('Y-m-d H:i:s'),
            'ends_at' => $this->faker->date('Y-m-d H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
