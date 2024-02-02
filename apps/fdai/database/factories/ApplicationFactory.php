<?php



namespace Database\Factories;

use App\AppSettings\AdditionalSettings;
use App\AppSettings\AppDesign;
use App\AppSettings\AppSettings;
use App\Properties\Application\ApplicationAppTypeProperty;
use App\Properties\Application\ApplicationStripePlanProperty;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Application;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;

class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $app = Application::whereClientId(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT)->first();
        if ($app) {
            //return $app->attributesToArray();
        }
        $as = new AppSettings();
        $as->clientId = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
        return [
            'additional_settings' => new AdditionalSettings($as),
            'address' => $this->faker->address,
            'app_description' => $this->faker->sentence(10),
            'app_design' =>  new AppDesign($as),
            'app_display_name' => $this->faker->sentence(3),
            'app_status' => null,
            'app_type' => ApplicationAppTypeProperty::APP_TYPE_DIET,
            'billing_enabled' => 0,
            'build_enabled' => 1,
            'city' => $this->faker->city(),
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            'company_name' => $this->faker->company(),
            'country' => $this->faker->country(),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null,
            'enabled' => 1,
            'exceeding_call_charge' => 1,
            'exceeding_call_count' => 1,
            'homepage_url' => $this->faker->url,
            'icon_url' => $this->faker->url,
            'last_four' => $this->faker->numberBetween(1000, 9999),
            'long_description' => $this->faker->word,
            'organization_id' => null,
            'outcome_variable_id' => null,
            'physician' => 0,
            'plan_id' => 0,
            'predictor_variable_id' => null,
            'splash_screen' => $this->faker->imageUrl(),
            'state' => "IL",
            //'status' => ApplicationStatusProperty::STATUS_ACTIVE,
            'stripe_active' => 1,
            'stripe_id' => $this->faker->randomDigitNotNull,
            'stripe_plan' => ApplicationStripePlanProperty::MONTHLY_PLAN,
            'stripe_subscription' => $this->faker->word,
            'study' => 0,
            'subscription_ends_at' => $this->faker->date('Y-m-d H:i:s'),
            'text_logo' => $this->faker->url,
            'trial_ends_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'user_id' => UserIdProperty::USER_ID_TEST_USER,
            'wp_post_id' => null,
            'zip' => $this->faker->postcode(),
        ];
    }
}
