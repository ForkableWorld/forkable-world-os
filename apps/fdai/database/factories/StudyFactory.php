<?php


namespace Database\Factories;

use App\Properties\Study\StudyCommentStatusProperty;
use App\Properties\Study\StudyStatusProperty;
use App\Properties\Study\StudyStudyStatusProperty;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UserVariableRelationship;
use App\Models\Study;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Study\StudyTypeProperty;

class StudyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $c = UserVariableRelationship::fakeFromPropertyModels();
        return [
            Study::FIELD_ID => $c->getStudyId(),
            'type' => StudyTypeProperty::TYPE_INDIVIDUAL,
            'cause_variable_id' => $c->cause_variable_id,
            'effect_variable_id' => $c->effect_variable_id,
            'user_id' => $c->user_id,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => null,
            Study::FIELD_IS_PUBLIC => $this->faker->boolean,
            'analysis_parameters' => $this->faker->text,
            'user_study_text' => $this->faker->text,
            'user_title' => $this->faker->text,
            'study_status' => StudyStudyStatusProperty::STATUS_PUBLISH,
            'comment_status' => StudyCommentStatusProperty::OPEN,
            'study_password' => $this->faker->word,
            'study_images' => $this->faker->image(),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            'published_at' => $this->faker->date('Y-m-d H:i:s'),
            'wp_post_id' => null,
            'newest_data_at' => $this->faker->date('Y-m-d H:i:s'),
            'analysis_requested_at' => $this->faker->date('Y-m-d H:i:s'),
            'reason_for_analysis' => $this->faker->word,
            'analysis_ended_at' => $this->faker->date('Y-m-d H:i:s'),
            'analysis_started_at' => $this->faker->date('Y-m-d H:i:s'),
            'internal_error_message' => $this->faker->word,
            'user_error_message' => $this->faker->word,
            'status' => StudyStatusProperty::STATUS_UPDATED,
            'analysis_settings_modified_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
