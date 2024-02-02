<?php



namespace Database\Factories;

use App\DataSources\Connectors\FitbitConnector;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Connection\ConnectionConnectStatusProperty;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Properties\User\UserIdProperty;

class ConnectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return $this->getData();
    }

    /**
     * @return array
     */
    public static function getData(): array
    {
        return [
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            'user_id' => UserIdProperty::USER_ID_TEST_USER,
            'connector_id' => FitbitConnector::ID,
            'connect_status' => ConnectionConnectStatusProperty::CONNECT_STATUS_DISCONNECTED,
            //'connect_error' => $this->faker->text,
            //'update_requested_at' => $this->faker->date('Y-m-d H:i:s'),
            //'update_status' => $this->faker->word,
            //'update_error' => $this->faker->text,
            //'last_successful_updated_at' => $this->faker->date('Y-m-d H:i:s'),
//            'created_at' => $this->faker->date('Y-m-d H:i:s'),
//            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
//            'deleted_at' => null,
//            'total_measurements_in_last_update' => $this->faker->randomDigitNotNull,
//            'user_message' => $this->faker->word,
//            'latest_measurement_at' => $this->faker->date('Y-m-d H:i:s'),
//            'import_started_at' => $this->faker->date('Y-m-d H:i:s'),
//            'import_ended_at' => $this->faker->date('Y-m-d H:i:s'),
//            'reason_for_import' => $this->faker->word,
//            'user_error_message' => $this->faker->word,
//            'internal_error_message' => $this->faker->word,
//            'wp_post_id' => null
        ];
    }
}
