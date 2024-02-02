<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Connector;

class ConnectorFactory extends Factory
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
            'display_name' => $this->faker->word,
            'image' => $this->faker->word,
            'get_it_url' => $this->faker->url,
            'short_description' => $this->faker->text,
            'long_description' => $this->faker->text,
            'enabled' => $this->faker->word,
            'oauth' => $this->faker->word,
            'qm_client' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'client_id' => \App\Models\OAClient::fakeFromPropertyModels()->client_id,
            'deleted_at' => null,
            'wp_post_id' => null
        ];
    }
}
