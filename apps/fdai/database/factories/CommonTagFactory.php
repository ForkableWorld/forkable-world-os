<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CommonTag;

class CommonTagFactory extends Factory
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
            'number_of_data_points' => $this->faker->randomDigitNotNull,
            'standard_error' => $this->faker->randomDigitNotNull,
            'tag_variable_unit_id' => \App\Units\OneToFiveRatingUnit::ID,
            'tagged_variable_unit_id' => \App\Units\OneToFiveRatingUnit::ID,
            'conversion_factor' => $this->faker->randomDigitNotNull,
            'client_id' => \App\Models\OAClient::fakeFromPropertyModels()->client_id,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null
        ];
    }
}
