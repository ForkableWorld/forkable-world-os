<?php



namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\WpPost;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BasePostStatusProperty;
use App\Properties\User\UserIdProperty;

class WpPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'post_author' => UserIdProperty::USER_ID_TEST_USER,
            'post_date' => $this->faker->dateTimeThisYear(),
            'post_date_gmt' => $this->faker->dateTimeThisYear(),
            'post_content' => "<!-- wp:html -->".$this->faker->realText(300)."<!-- /wp:html -->",
            'post_title' => $this->faker->text,
            'post_excerpt' => $this->faker->text,
            'post_status' => BasePostStatusProperty::STATUS_PUBLISH,
            'comment_status' => $this->faker->word,
            'ping_status' => $this->faker->word,
            'post_password' => $this->faker->word,
            'post_name' => $this->faker->word."-".$this->faker->word,
            'to_ping' => $this->faker->text,
            'pinged' => $this->faker->text,
            'post_modified' => $this->faker->dateTimeThisYear(),
            'post_modified_gmt' => $this->faker->dateTimeThisYear(),
            'post_content_filtered' => null,
            'post_parent' => null,
            'guid' => $this->faker->word,
            'menu_order' => $this->faker->randomDigitNotNull,
            'post_type' => $this->faker->word,
            'post_mime_type' => $this->faker->word,
            'comment_count' => $this->faker->word,
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null,
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ];
    }
}
