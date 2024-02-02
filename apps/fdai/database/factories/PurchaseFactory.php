<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Purchase;
use App\Properties\User\UserIdProperty;

class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'subscriber_user_id' => UserIdProperty::USER_ID_TEST_USER,
            'referrer_user_id' => UserIdProperty::USER_ID_TEST_USER,
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'subscription_provider' => $this->faker->word,
            'last_four' => $this->faker->word,
            'product_id' => $this->faker->randomDigitNotNull,
            'subscription_provider_transaction_id' => $this->faker->randomDigitNotNull,
            'coupon' => $this->faker->word,
            'client_id' => \App\Models\OAClient::fakeFromPropertyModels()->client_id,
            'refunded_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null
        ];
    }
}
