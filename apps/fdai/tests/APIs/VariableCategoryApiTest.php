<?php namespace Tests\APIs;

use App\Exceptions\UnauthorizedException;
use App\Types\QMStr;
use App\VariableCategories\EmotionsVariableCategory;
use Tests\QMBaseTestCase;
use Tests\UnitTestCase;
use Tests\ApiTestTrait;
use App\Models\VariableCategory;

class VariableCategoryApiTest extends UnitTestCase
{
    use ApiTestTrait;

    public function test_create_variable_category()
    {
        QMBaseTestCase::setExpectedRequestException(UnauthorizedException::class);
        $variableCategory = VariableCategory::factory()->make()->toArray();

        $r = $this->jsonAsUser18535(
            'POST',
            '/api/v6/variable_categories', $variableCategory
        );

        $r->assertStatus(401);
    }

    /**
     * @return void
     * @covers \App\Http\Controllers\API\VariableCategoryAPIController::find()
     */
    public function test_read_variable_category()
    {
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/variable_categories/'.EmotionsVariableCategory::ID
        );

        $responseData = $this->getDecodedResponseContent();
        $this->assertContains(array(
            'amazon_product_category' => '',
            'average_seconds_between_measurements' => NULL,
            'boring' => false,
            'cause_only' => false,
            'combination_operation' => 'MEAN',
            'controllable' => 'SOMETIMES',
            'created_at' => '2020-01-01 00:00:00',
            'default_unit_id' => 10,
            'deleted_at' => NULL,
            'duration_of_action' => 86400,
            'effect_only' => NULL,
            'filling_type' => 'none',
            'filling_value' => -1,
            'font_awesome' => 'far fa-grin-tongue-wink',
            'id' => 1,
            'image_url' => 'https://static.quantimo.do/img/variable_categories/theatre_mask-96.png',
            'ion_icon' => 'ion-happy-outline',
            'is_goal' => 'SOMETIMES',
            'is_public' => 1,
            'manual_tracking' => true,
            'maximum_allowed_value' => NULL,
            'median_seconds_between_measurements' => NULL,
            'minimum_allowed_seconds_between_measurements' => 60,
            'minimum_allowed_value' => NULL,
            'more_info' => 'Do you have any emotions that fluctuate regularly?  If so, add them so I can try to determine which factors are influencing them.',
            'name' => 'Emotions',
            'name_singular' => '',
            'number_of_measurements' => NULL,
            'number_of_outcome_case_studies' => NULL,
            'number_of_outcome_population_studies' => NULL,
            'number_of_predictor_case_studies' => NULL,
            'number_of_predictor_population_studies' => NULL,
            'number_of_user_variables' => NULL,
            'number_of_variables' => NULL,
            'onset_delay' => 0,
            'outcome' => true,
            'predictor' => false,
            'slug' => 'emotions',
            'sort_order' => 0,
            'synonyms' =>
                array (
                    0 => 'Emotions',
                    1 => 'Emotion',
                    2 => 'Mood',
                ),
            'valence' => 'neutral',
            'wp_post_id' => NULL,
        ), (array)$responseData->data);
        $this->assertTrue(true);

    }

    public function test_update_variable_category()
    {
        QMBaseTestCase::setExpectedRequestException(UnauthorizedException::class);

        $r = $this->jsonAsUser18535(
            'PUT',
            '/api/v6/variable_categories/'.EmotionsVariableCategory::ID,
            ['name' => QMStr::random()]
        );

        $this->testResponse->assertStatus(401);
    }

    public function test_delete_variable_category()
    {
        $cat = VariableCategory::find(EmotionsVariableCategory::ID);
        QMBaseTestCase::setExpectedRequestException(UnauthorizedException::class);

        $r = $this->jsonAsUser18535(
            'DELETE',
             $this->getV6BasePathForClassTested().'/'.EmotionsVariableCategory::ID
         );

        $r->assertStatus(401);
        $r = $this->jsonAsUser18535(
            'GET',
            $this->getV6BasePathForClassTested().'/'.EmotionsVariableCategory::ID
        );

        $r->assertStatus(200);
    }
}
