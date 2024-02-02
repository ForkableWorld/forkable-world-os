<?php



namespace Database\Factories;

use App\Models\Application;
use App\Properties\Collaborator\CollaboratorTypeProperty;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Collaborator;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;

class CollaboratorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $app = Application::firstOrFakeSave();
        return [
            'user_id' => UserIdProperty::USER_ID_TEST_USER,
            'app_id' => $app->id,
            'type' => CollaboratorTypeProperty::TYPE_OWNER,
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ];
    }
}
