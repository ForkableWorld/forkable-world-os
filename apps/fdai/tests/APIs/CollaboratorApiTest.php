<?php namespace Tests\APIs;

use App\Models\Application;
use App\Properties\Collaborator\CollaboratorTypeProperty;
use Tests\UnitTestCase;
use Tests\ApiTestTrait;
use App\Models\Collaborator;

class CollaboratorApiTest extends UnitTestCase
{
    use ApiTestTrait;

    public function test_create_collaborator()
    {
        $this->skipTest('Not implemented yet.');
        Collaborator::deleteAll();
        $toCreate = Collaborator::factory()->make()->toArray();
        $app = Application::find($toCreate['app_id']);
        $this->createAndGetApiV6AsTestUser($toCreate);
        $this->updateAttributeApiV6(CollaboratorTypeProperty::TYPE_COLLABORATOR,
            CollaboratorTypeProperty::NAME);
        $collaborator = Collaborator::fakeSaveFromPropertyModels();
        $this->deleteApiV6($collaborator);
    }
}
