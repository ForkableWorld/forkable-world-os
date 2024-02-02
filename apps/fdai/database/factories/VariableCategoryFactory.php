<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\VariableCategory;

class VariableCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'filling_value' => $this->faker->randomDigitNotNull,
            'maximum_allowed_value' => $this->faker->randomDigitNotNull,
            'minimum_allowed_value' => $this->faker->randomDigitNotNull,
            'onset_delay' => 3600,
            'combination_operation' => $this->faker->word,
            //'updated' => $this->faker->randomDigitNotNull,
            'cause_only' => $this->faker->word,
            VariableCategory::FIELD_IS_PUBLIC => true,
            'outcome' => $this->faker->word,
//            'created_at' => $this->faker->date('Y-m-d H:i:s'),
//            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'image_url' => $this->faker->text,
            'default_unit_id' => \App\Units\OneToFiveRatingUnit::ID,
            'deleted_at' => null,
            'manual_tracking' => $this->faker->word,
            'minimum_allowed_seconds_between_measurements' => $this->faker->randomDigitNotNull,
            'average_seconds_between_measurements' => $this->faker->randomDigitNotNull,
            'median_seconds_between_measurements' => $this->faker->randomDigitNotNull,
            //'wp_post_id' => null
        ];
    }
}
