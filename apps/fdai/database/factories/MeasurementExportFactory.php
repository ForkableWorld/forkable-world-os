<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MeasurementExport;
use App\Properties\User\UserIdProperty;

class MeasurementExportFactory extends Factory
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
            'client_id' => \App\Models\OAClient::fakeFromPropertyModels()->client_id,
            'status' => MeasurementExport::STATUS_WAITING,
            'type' => $this->faker->word,
            'output_type' => $this->faker->word,
            'error_message' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null
        ];
    }
}
