<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ConnectorImport;

class ConnectorImportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $connection = \App\Models\Connection::firstOrFakeNew();
        return [
            'client_id' => \App\Models\OAClient::fakeFromPropertyModels()->client_id,
            'connection_id' => $connection->id,
            'connector_id' => $connection->connector_id,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null,
            'earliest_measurement_at' => $this->faker->date('Y-m-d H:i:s'),
            'import_ended_at' => $this->faker->date('Y-m-d H:i:s'),
            'import_started_at' => $this->faker->date('Y-m-d H:i:s'),
            'internal_error_message' => $this->faker->word,
            'latest_measurement_at' => $this->faker->date('Y-m-d H:i:s'),
            'number_of_measurements' => $this->faker->randomDigitNotNull,
            'reason_for_import' => $this->faker->word,
            'success' => true,
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'user_error_message' => $this->faker->word,
            'user_id' => $connection->user_id,
            'additional_meta_data' => $this->faker->text
        ];
    }
}
