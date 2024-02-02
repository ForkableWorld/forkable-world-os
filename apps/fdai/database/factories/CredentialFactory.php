<?php

namespace Database\Factories;

use App\DataSources\Connectors\FitbitConnector;
use App\Models\Credential;
use App\Properties\User\UserIdProperty;
use Illuminate\Database\Eloquent\Factories\Factory;

class CredentialFactory extends Factory
{
    protected $model = Credential::class;

    public function definition(): array
    {
        return [
             Credential::FIELD_EXPIRES_AT => db_date(time()),
             Credential::FIELD_CREATED_AT => db_date(time()),
             Credential::FIELD_UPDATED_AT => db_date(time()),
             Credential::FIELD_USER_ID => UserIdProperty::USER_ID_TEST_USER,
             Credential::FIELD_CONNECTOR_ID => FitbitConnector::ID,
             Credential::FIELD_ATTR_VALUE => '6025',
             Credential::FIELD_ATTR_KEY => 'zip'
        ];
    }
}
