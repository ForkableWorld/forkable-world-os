<?php


namespace Database\Factories;

use App\Properties\Base\BaseClientIdProperty;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\DataSources\Connectors\FitbitConnector;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;

class MeasurementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return MeasurementFactory::getData();
    }

    /**
     * @return array
     */
    public static function getData(): array
    {
        $uv = OverallMoodCommonVariable::instance();
        return [
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            //'connection_id' => $this->faker->randomDigitNotNull,
            //'connector_id' => FitbitConnector::ID,
            //'connector_import_id' => $this->faker->randomDigitNotNull,
//            'created_at' =>  db_date(time() - 1),
//            'deleted_at' => null,
            'duration' => 1,
//            'error' => $this->faker->sentence(3),
//            'latitude' => $this->faker->randomDigitNotNull,
//            'location' => $this->faker->word,
//            'longitude' => $this->faker->randomDigitNotNull,
            'note' => "I am a note",
//            'original_unit_id' => $uv->unitId,
//            'original_value' => 3,
            //'source_name' => FitbitConnector::NAME,
            'start_at' => db_date(time() - 1),
            //'start_time' => time() - 1,
            'unit_id' => $uv->getUnitIdAttribute(),
            //'updated_at' => db_date(time() - 1),
            //'user_id' => $uv->userId,
            //'user_variable_id' => $uv->id,
            'value' => 3,
            //'variable_category_id' => $uv->variableCategoryId,
            'variable_id' => $uv->getVariableId(),
        ];
    }
}
