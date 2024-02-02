<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ConnectorRequest;
use App\Properties\User\UserIdProperty;

class ConnectorRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'connector_id' => \App\DataSources\Connectors\FitbitConnector::ID,
            'user_id' => UserIdProperty::USER_ID_TEST_USER,
            'connection_id' => $this->faker->randomDigitNotNull,
            'connector_import_id' => $this->faker->randomDigitNotNull,
            'method' => $this->faker->word,
            'code' => $this->faker->randomDigitNotNull,
            'uri' => $this->faker->word,
            'response_body' => $this->faker->text,
            'request_body' => $this->faker->text,
            'request_headers' => $this->faker->text,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null,
            'content_type' => $this->faker->word
        ];
    }
}
