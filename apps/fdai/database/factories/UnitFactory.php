<?php



namespace Database\Factories;

use App\Properties\Base\BaseFillingTypeProperty;
use App\UnitCategories\TemperatureUnitCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Unit;

class UnitFactory extends Factory
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
            'abbreviated_name' => $this->faker->word,
            'unit_category_id' => TemperatureUnitCategory::ID,
            'minimum_value' => $this->faker->randomDigitNotNull,
            'maximum_value' => $this->faker->randomDigitNotNull,
            //'multiply' => $this->faker->randomDigitNotNull,
            //'add' => $this->faker->randomDigitNotNull,
            //'created_at' => $this->faker->date('Y-m-d H:i:s'),
            //'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null,
            'filling_type' => BaseFillingTypeProperty::FILLING_TYPE_ZERO,
            'number_of_outcome_population_studies' => $this->faker->randomDigitNotNull,
            'number_of_common_tags_where_tag_variable_unit' => $this->faker->randomDigitNotNull,
            'number_of_common_tags_where_tagged_variable_unit' => $this->faker->randomDigitNotNull,
            'number_of_outcome_case_studies' => $this->faker->randomDigitNotNull,
            'number_of_measurements' => $this->faker->randomDigitNotNull,
            'number_of_user_variables_where_default_unit' => $this->faker->randomDigitNotNull,
            'number_of_variable_categories_where_default_unit' => $this->faker->randomDigitNotNull,
            'number_of_variables_where_default_unit' => $this->faker->randomDigitNotNull
        ];
    }
}
