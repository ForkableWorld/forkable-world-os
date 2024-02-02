<?php



namespace Database\Factories;

use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OAClient;
use App\Properties\User\UserIdProperty;

class OAClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::testUser();
        $user->save();
        return [
            'client_secret' => BaseClientSecretProperty::TEST_CLIENT_SECRET,
            'client_id' =>  BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            'redirect_uri' => $this->faker->url(),
            'grant_types' => $this->faker->word,
            'user_id' => $user->getId(),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'icon_url' => $this->faker->url,
            'app_identifier' => $this->faker->word,
            'deleted_at' => null,
            'earliest_measurement_start_at' => $this->faker->date('Y-m-d H:i:s'),
            'latest_measurement_start_at' => $this->faker->date('Y-m-d H:i:s'),
            'number_of_global_variable_relationships' => $this->faker->randomDigitNotNull,
            'number_of_applications' => $this->faker->randomDigitNotNull,
            'number_of_oauth_access_tokens' => $this->faker->randomDigitNotNull,
            'number_of_oauth_authorization_codes' => $this->faker->randomDigitNotNull,
            'number_of_oauth_refresh_tokens' => $this->faker->randomDigitNotNull,
            'number_of_button_clicks' => $this->faker->randomDigitNotNull,
            'number_of_collaborators' => $this->faker->randomDigitNotNull,
            'number_of_common_tags' => $this->faker->randomDigitNotNull,
            'number_of_connections' => $this->faker->randomDigitNotNull,
            'number_of_connector_imports' => $this->faker->randomDigitNotNull,
            'number_of_connectors' => $this->faker->randomDigitNotNull,
            'number_of_correlations' => $this->faker->randomDigitNotNull
        ];
    }
}
