<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CorrelationResource\Pages;
use App\Filament\Resources\CorrelationResource\RelationManagers;
use App\Models\UserVariableRelationship;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CorrelationResource extends Resource
{
    protected static ?string $model = UserVariableRelationship::class;
	protected static ?string $modelLabel = UserVariableRelationship::CLASS_DISPLAY_NAME;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID')
                    ->required(),
                Forms\Components\Select::make('cause_variable_id')
                    ->relationship('cause_variable', 'name')
                    ->required(),
                Forms\Components\Select::make('effect_variable_id')
                    ->relationship('effect_variable', 'name')
                    ->required(),
                Forms\Components\Select::make('cause_unit_id')
                    ->relationship('cause_unit', 'name'),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id'),
                Forms\Components\Select::make('wp_post_id')
                    ->relationship('wp_post', 'ID'),
                Forms\Components\Select::make('cause_variable_category_id')
                    ->relationship('cause_variable_category', 'name')
                    ->required(),
                Forms\Components\Select::make('effect_variable_category_id')
                    ->relationship('effect_variable_category', 'name')
                    ->required(),
                Forms\Components\Select::make('cause_user_variable_id')
                    ->relationship('cause_user_variable', 'id')
                    ->required(),
                Forms\Components\Select::make('effect_user_variable_id')
                    ->relationship('effect_user_variable', 'id')
                    ->required(),
                Forms\Components\Select::make('global_variable_relationship_id')
                    ->relationship('global_variable_relationship', 'id'),
                Forms\Components\TextInput::make('qm_score'),
                Forms\Components\TextInput::make('forward_pearson_correlation_coefficient'),
                Forms\Components\TextInput::make('value_predicting_high_outcome'),
                Forms\Components\TextInput::make('value_predicting_low_outcome'),
                Forms\Components\TextInput::make('predicts_high_effect_change'),
                Forms\Components\TextInput::make('predicts_low_effect_change')
                    ->required(),
                Forms\Components\TextInput::make('average_effect')
                    ->required(),
                Forms\Components\TextInput::make('average_effect_following_high_cause')
                    ->required(),
                Forms\Components\TextInput::make('average_effect_following_low_cause')
                    ->required(),
                Forms\Components\TextInput::make('average_daily_low_cause')
                    ->required(),
                Forms\Components\TextInput::make('average_daily_high_cause')
                    ->required(),
                Forms\Components\TextInput::make('average_forward_pearson_correlation_over_onset_delays'),
                Forms\Components\TextInput::make('average_reverse_pearson_correlation_over_onset_delays'),
                Forms\Components\TextInput::make('cause_changes')
                    ->required(),
                Forms\Components\TextInput::make('cause_filling_value'),
                Forms\Components\TextInput::make('cause_number_of_processed_daily_measurements')
                    ->required(),
                Forms\Components\TextInput::make('cause_number_of_raw_measurements')
                    ->required(),
                Forms\Components\TextInput::make('confidence_interval')
                    ->required(),
                Forms\Components\TextInput::make('critical_t_value')
                    ->required(),
                Forms\Components\TextInput::make('data_source_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('duration_of_action')
                    ->required(),
                Forms\Components\TextInput::make('effect_changes')
                    ->required(),
                Forms\Components\TextInput::make('effect_filling_value'),
                Forms\Components\TextInput::make('effect_number_of_processed_daily_measurements')
                    ->required(),
                Forms\Components\TextInput::make('effect_number_of_raw_measurements')
                    ->required(),
                Forms\Components\TextInput::make('forward_spearman_correlation_coefficient')
                    ->required(),
                Forms\Components\TextInput::make('number_of_days')
                    ->required(),
                Forms\Components\TextInput::make('number_of_pairs')
                    ->required(),
                Forms\Components\TextInput::make('onset_delay')
                    ->required(),
                Forms\Components\TextInput::make('onset_delay_with_strongest_pearson_correlation'),
                Forms\Components\TextInput::make('optimal_pearson_product'),
                Forms\Components\TextInput::make('p_value'),
                Forms\Components\TextInput::make('pearson_correlation_with_no_onset_delay'),
                Forms\Components\TextInput::make('predictive_pearson_correlation_coefficient'),
                Forms\Components\TextInput::make('reverse_pearson_correlation_coefficient'),
                Forms\Components\TextInput::make('statistical_significance'),
                Forms\Components\TextInput::make('strongest_pearson_correlation_coefficient'),
                Forms\Components\TextInput::make('t_value'),
                Forms\Components\TextInput::make('grouped_cause_value_closest_to_value_predicting_low_outcome')
                    ->required(),
                Forms\Components\TextInput::make('grouped_cause_value_closest_to_value_predicting_high_outcome')
                    ->required(),
                Forms\Components\DateTimePicker::make('published_at'),
                Forms\Components\TextInput::make('status')
                    ->maxLength(25),
                Forms\Components\Toggle::make('interesting_variable_category_pair')
                    ->required(),
                Forms\Components\DateTimePicker::make('newest_data_at'),
                Forms\Components\DateTimePicker::make('analysis_requested_at'),
                Forms\Components\TextInput::make('reason_for_analysis')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('analysis_started_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('analysis_ended_at'),
                Forms\Components\Textarea::make('user_error_message')
                    ->maxLength(65535),
                Forms\Components\Textarea::make('internal_error_message')
                    ->maxLength(65535),
                Forms\Components\DateTimePicker::make('latest_measurement_start_at'),
                Forms\Components\DateTimePicker::make('earliest_measurement_start_at'),
                Forms\Components\TextInput::make('cause_baseline_average_per_day')
                    ->required(),
                Forms\Components\TextInput::make('cause_baseline_average_per_duration_of_action')
                    ->required(),
                Forms\Components\TextInput::make('cause_treatment_average_per_day')
                    ->required(),
                Forms\Components\TextInput::make('cause_treatment_average_per_duration_of_action')
                    ->required(),
                Forms\Components\TextInput::make('effect_baseline_average'),
                Forms\Components\TextInput::make('effect_baseline_relative_standard_deviation')
                    ->required(),
                Forms\Components\TextInput::make('effect_baseline_standard_deviation'),
                Forms\Components\TextInput::make('effect_follow_up_average')
                    ->required(),
                Forms\Components\TextInput::make('effect_follow_up_percent_change_from_baseline')
                    ->required(),
                Forms\Components\TextInput::make('z_score'),
                Forms\Components\DateTimePicker::make('experiment_end_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('experiment_start_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('aggregated_at'),
                Forms\Components\TextInput::make('usefulness_vote'),
                Forms\Components\TextInput::make('causality_vote'),
                Forms\Components\TextInput::make('deletion_reason')
                    ->maxLength(280),
                Forms\Components\TextInput::make('record_size_in_kb'),
                Forms\Components\Textarea::make('correlations_over_durations')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\Textarea::make('correlations_over_delays')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\Toggle::make('is_public'),
                Forms\Components\TextInput::make('sort_order')
                    ->required(),
                Forms\Components\Toggle::make('boring'),
                Forms\Components\Toggle::make('outcome_is_goal'),
                Forms\Components\Toggle::make('predictor_is_controllable'),
                Forms\Components\Toggle::make('plausibly_causal'),
                Forms\Components\Toggle::make('obvious'),
                Forms\Components\TextInput::make('number_of_up_votes')
                    ->required(),
                Forms\Components\TextInput::make('number_of_down_votes')
                    ->required(),
                Forms\Components\TextInput::make('strength_level')
                    ->required(),
                Forms\Components\TextInput::make('confidence_level')
                    ->required(),
                Forms\Components\TextInput::make('relationship')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->maxLength(200),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.ID'),
                Tables\Columns\TextColumn::make('cause_variable.name'),
                Tables\Columns\TextColumn::make('effect_variable.name'),
                Tables\Columns\TextColumn::make('cause_unit.name'),
                Tables\Columns\TextColumn::make('client.client_id'),
                Tables\Columns\TextColumn::make('wp_post.ID'),
                Tables\Columns\TextColumn::make('cause_variable_category.name'),
                Tables\Columns\TextColumn::make('effect_variable_category.name'),
                Tables\Columns\TextColumn::make('cause_user_variable.id'),
                Tables\Columns\TextColumn::make('effect_user_variable.id'),
                Tables\Columns\TextColumn::make('global_variable_relationship.id'),
                Tables\Columns\TextColumn::make('qm_score'),
                Tables\Columns\TextColumn::make('forward_pearson_correlation_coefficient'),
                Tables\Columns\TextColumn::make('value_predicting_high_outcome'),
                Tables\Columns\TextColumn::make('value_predicting_low_outcome'),
                Tables\Columns\TextColumn::make('predicts_high_effect_change'),
                Tables\Columns\TextColumn::make('predicts_low_effect_change'),
                Tables\Columns\TextColumn::make('average_effect'),
                Tables\Columns\TextColumn::make('average_effect_following_high_cause'),
                Tables\Columns\TextColumn::make('average_effect_following_low_cause'),
                Tables\Columns\TextColumn::make('average_daily_low_cause'),
                Tables\Columns\TextColumn::make('average_daily_high_cause'),
                Tables\Columns\TextColumn::make('average_forward_pearson_correlation_over_onset_delays'),
                Tables\Columns\TextColumn::make('average_reverse_pearson_correlation_over_onset_delays'),
                Tables\Columns\TextColumn::make('cause_changes'),
                Tables\Columns\TextColumn::make('cause_filling_value'),
                Tables\Columns\TextColumn::make('cause_number_of_processed_daily_measurements'),
                Tables\Columns\TextColumn::make('cause_number_of_raw_measurements'),
                Tables\Columns\TextColumn::make('confidence_interval'),
                Tables\Columns\TextColumn::make('critical_t_value'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('data_source_name'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('duration_of_action'),
                Tables\Columns\TextColumn::make('effect_changes'),
                Tables\Columns\TextColumn::make('effect_filling_value'),
                Tables\Columns\TextColumn::make('effect_number_of_processed_daily_measurements'),
                Tables\Columns\TextColumn::make('effect_number_of_raw_measurements'),
                Tables\Columns\TextColumn::make('forward_spearman_correlation_coefficient'),
                Tables\Columns\TextColumn::make('number_of_days'),
                Tables\Columns\TextColumn::make('number_of_pairs'),
                Tables\Columns\TextColumn::make('onset_delay'),
                Tables\Columns\TextColumn::make('onset_delay_with_strongest_pearson_correlation'),
                Tables\Columns\TextColumn::make('optimal_pearson_product'),
                Tables\Columns\TextColumn::make('p_value'),
                Tables\Columns\TextColumn::make('pearson_correlation_with_no_onset_delay'),
                Tables\Columns\TextColumn::make('predictive_pearson_correlation_coefficient'),
                Tables\Columns\TextColumn::make('reverse_pearson_correlation_coefficient'),
                Tables\Columns\TextColumn::make('statistical_significance'),
                Tables\Columns\TextColumn::make('strongest_pearson_correlation_coefficient'),
                Tables\Columns\TextColumn::make('t_value'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('grouped_cause_value_closest_to_value_predicting_low_outcome'),
                Tables\Columns\TextColumn::make('grouped_cause_value_closest_to_value_predicting_high_outcome'),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\IconColumn::make('interesting_variable_category_pair')
                    ->boolean(),
                Tables\Columns\TextColumn::make('newest_data_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('analysis_requested_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('reason_for_analysis'),
                Tables\Columns\TextColumn::make('analysis_started_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('analysis_ended_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('user_error_message'),
                Tables\Columns\TextColumn::make('internal_error_message'),
                Tables\Columns\TextColumn::make('latest_measurement_start_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('earliest_measurement_start_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('cause_baseline_average_per_day'),
                Tables\Columns\TextColumn::make('cause_baseline_average_per_duration_of_action'),
                Tables\Columns\TextColumn::make('cause_treatment_average_per_day'),
                Tables\Columns\TextColumn::make('cause_treatment_average_per_duration_of_action'),
                Tables\Columns\TextColumn::make('effect_baseline_average'),
                Tables\Columns\TextColumn::make('effect_baseline_relative_standard_deviation'),
                Tables\Columns\TextColumn::make('effect_baseline_standard_deviation'),
                Tables\Columns\TextColumn::make('effect_follow_up_average'),
                Tables\Columns\TextColumn::make('effect_follow_up_percent_change_from_baseline'),
                Tables\Columns\TextColumn::make('z_score'),
                Tables\Columns\TextColumn::make('experiment_end_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('experiment_start_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('aggregated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('usefulness_vote'),
                Tables\Columns\TextColumn::make('causality_vote'),
                Tables\Columns\TextColumn::make('deletion_reason'),
                Tables\Columns\TextColumn::make('record_size_in_kb'),
                Tables\Columns\TextColumn::make('correlations_over_durations'),
                Tables\Columns\TextColumn::make('correlations_over_delays'),
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order'),
                Tables\Columns\IconColumn::make('boring')
                    ->boolean(),
                Tables\Columns\IconColumn::make('outcome_is_goal')
                    ->boolean(),
                Tables\Columns\IconColumn::make('predictor_is_controllable')
                    ->boolean(),
                Tables\Columns\IconColumn::make('plausibly_causal')
                    ->boolean(),
                Tables\Columns\IconColumn::make('obvious')
                    ->boolean(),
                Tables\Columns\TextColumn::make('number_of_up_votes'),
                Tables\Columns\TextColumn::make('number_of_down_votes'),
                Tables\Columns\TextColumn::make('strength_level'),
                Tables\Columns\TextColumn::make('confidence_level'),
                Tables\Columns\TextColumn::make('relationship'),
                Tables\Columns\TextColumn::make('slug'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCorrelations::route('/'),
            'edit' => Pages\EditCorrelation::route('/{record}/edit'),
        ];
    }    
}
