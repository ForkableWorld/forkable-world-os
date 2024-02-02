<?php



namespace Database\Factories;

use App\DataSources\Connectors\FitbitConnector;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\MeasurementImport;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;

class MeasurementImportFactory extends Factory
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
            'file' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'status' => \App\Models\MeasurementExport::STATUS_WAITING,
            'error_message' => null,
            'source_name' => FitbitConnector::NAME,
            'deleted_at' => null,
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ];
    }
}
