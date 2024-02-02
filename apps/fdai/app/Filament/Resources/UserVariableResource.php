<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserVariableResource\Pages;
use App\Filament\Resources\UserVariableResource\RelationManagers;
use App\Models\UserVariable;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserVariableResource extends Resource
{
    protected static ?string $model = UserVariable::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
//                Forms\Components\Select::make('client_id')
//                    ->relationship('client', 'client_id'),
//                Forms\Components\Select::make('user_id')
//                    ->relationship('user', 'ID')
//                    ->required(),
                Forms\Components\Select::make('variable_id')
                    ->relationship('variable', 'name')
                    ->required(),
//                Forms\Components\Select::make('default_unit_id')
//                    ->relationship('default_unit', 'name'),
//                Forms\Components\Select::make('variable_category_id')
//                    ->relationship('variable_category', 'name'),
//                Forms\Components\Select::make('last_unit_id')
//                    ->relationship('last_unit', 'name'),
//                Forms\Components\Select::make('wp_post_id')
//                    ->relationship('wp_post', 'ID'),
//                Forms\Components\Select::make('best_user_variable_relationship_id')
//                    ->relationship('best_user_variable_relationship', 'id'),
//                Forms\Components\TextInput::make('parent_id'),
//                Forms\Components\TextInput::make('minimum_allowed_value'),
//                Forms\Components\TextInput::make('maximum_allowed_value'),
//                Forms\Components\TextInput::make('filling_value'),
//                Forms\Components\TextInput::make('join_with'),
//                Forms\Components\TextInput::make('onset_delay'),
//                Forms\Components\TextInput::make('duration_of_action'),
//                Forms\Components\Toggle::make('cause_only'),
//                Forms\Components\TextInput::make('filling_type'),
//                Forms\Components\TextInput::make('number_of_processed_daily_measurements'),
//                Forms\Components\TextInput::make('measurements_at_last_analysis')
//                    ->required(),
//                Forms\Components\TextInput::make('last_original_unit_id'),
//                Forms\Components\TextInput::make('last_value'),
//                Forms\Components\TextInput::make('last_original_value'),
//                Forms\Components\TextInput::make('number_of_correlations'),
//                Forms\Components\TextInput::make('status')
//                    ->maxLength(25),
//                Forms\Components\TextInput::make('standard_deviation'),
//                Forms\Components\TextInput::make('variance'),
//                Forms\Components\TextInput::make('minimum_recorded_value'),
//                Forms\Components\TextInput::make('maximum_recorded_value'),
//                Forms\Components\TextInput::make('mean'),
//                Forms\Components\TextInput::make('median'),
//                Forms\Components\TextInput::make('most_common_original_unit_id'),
//                Forms\Components\TextInput::make('most_common_value'),
//                Forms\Components\TextInput::make('number_of_unique_daily_values'),
//                Forms\Components\TextInput::make('number_of_unique_values'),
//                Forms\Components\TextInput::make('number_of_changes'),
//                Forms\Components\TextInput::make('skewness'),
//                Forms\Components\TextInput::make('kurtosis'),
//                Forms\Components\TextInput::make('latitude'),
//                Forms\Components\TextInput::make('longitude'),
//                Forms\Components\TextInput::make('location')
//                    ->maxLength(255),
//                Forms\Components\Toggle::make('outcome'),
//                Forms\Components\Textarea::make('data_sources_count')
//                    ->maxLength(65535),
//                Forms\Components\TextInput::make('earliest_filling_time'),
//                Forms\Components\TextInput::make('latest_filling_time'),
//                Forms\Components\TextInput::make('last_processed_daily_value'),
//                Forms\Components\Toggle::make('outcome_of_interest'),
//                Forms\Components\Toggle::make('predictor_of_interest'),
//                Forms\Components\DateTimePicker::make('experiment_start_time'),
//                Forms\Components\DateTimePicker::make('experiment_end_time'),
//                Forms\Components\Textarea::make('description')
//                    ->maxLength(65535),
//                Forms\Components\TextInput::make('alias')
//                    ->maxLength(125),
//                Forms\Components\TextInput::make('second_to_last_value'),
//                Forms\Components\TextInput::make('third_to_last_value'),
//                Forms\Components\TextInput::make('number_of_user_variable_relationships_as_effect'),
//                Forms\Components\TextInput::make('number_of_user_variable_relationships_as_cause'),
//                Forms\Components\TextInput::make('combination_operation'),
//                Forms\Components\TextInput::make('informational_url')
//                    ->maxLength(2000),
//                Forms\Components\Select::make('most_common_connector_id')
//                    ->relationship('most_common_connector', 'name'),
//                Forms\Components\TextInput::make('valence'),
//                Forms\Components\TextInput::make('wikipedia_title')
//                    ->maxLength(100),
//                Forms\Components\TextInput::make('number_of_tracking_reminders')
//                    ->required(),
//                Forms\Components\TextInput::make('number_of_raw_measurements_with_tags_joins_children'),
//                Forms\Components\TextInput::make('most_common_source_name')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('optimal_value_message')
//                    ->maxLength(500),
//                Forms\Components\Select::make('best_cause_variable_id')
//                    ->relationship('best_cause_variable', 'name'),
//                Forms\Components\Select::make('best_effect_variable_id')
//                    ->relationship('best_effect_variable', 'name'),
//                Forms\Components\TextInput::make('user_maximum_allowed_daily_value'),
//                Forms\Components\TextInput::make('user_minimum_allowed_daily_value'),
//                Forms\Components\TextInput::make('user_minimum_allowed_non_zero_value'),
//                Forms\Components\TextInput::make('minimum_allowed_seconds_between_measurements'),
//                Forms\Components\TextInput::make('average_seconds_between_measurements'),
//                Forms\Components\TextInput::make('median_seconds_between_measurements'),
//                Forms\Components\DateTimePicker::make('last_correlated_at'),
//                Forms\Components\TextInput::make('number_of_measurements_with_tags_at_last_correlation'),
//                Forms\Components\DateTimePicker::make('analysis_settings_modified_at'),
//                Forms\Components\DateTimePicker::make('newest_data_at'),
//                Forms\Components\DateTimePicker::make('analysis_requested_at'),
//                Forms\Components\TextInput::make('reason_for_analysis')
//                    ->maxLength(255),
//                Forms\Components\DateTimePicker::make('analysis_started_at'),
//                Forms\Components\DateTimePicker::make('analysis_ended_at'),
//                Forms\Components\Textarea::make('user_error_message')
//                    ->maxLength(65535),
//                Forms\Components\Textarea::make('internal_error_message')
//                    ->maxLength(65535),
//                Forms\Components\DateTimePicker::make('earliest_source_measurement_start_at'),
//                Forms\Components\DateTimePicker::make('latest_source_measurement_start_at'),
//                Forms\Components\DateTimePicker::make('latest_tagged_measurement_start_at'),
//                Forms\Components\DateTimePicker::make('earliest_tagged_measurement_start_at'),
//                Forms\Components\DateTimePicker::make('latest_non_tagged_measurement_start_at'),
//                Forms\Components\DateTimePicker::make('earliest_non_tagged_measurement_start_at'),
//                Forms\Components\TextInput::make('number_of_soft_deleted_measurements'),
//                Forms\Components\TextInput::make('number_of_measurements'),
//                Forms\Components\TextInput::make('number_of_tracking_reminder_notifications'),
//                Forms\Components\TextInput::make('deletion_reason')
//                    ->maxLength(280),
//                Forms\Components\TextInput::make('record_size_in_kb'),
//                Forms\Components\TextInput::make('number_of_common_tags'),
//                Forms\Components\TextInput::make('number_common_tagged_by'),
//                Forms\Components\TextInput::make('number_of_common_joined_variables'),
//                Forms\Components\TextInput::make('number_of_common_ingredients'),
//                Forms\Components\TextInput::make('number_of_common_foods'),
//                Forms\Components\TextInput::make('number_of_common_children'),
//                Forms\Components\TextInput::make('number_of_common_parents'),
//                Forms\Components\TextInput::make('number_of_user_tags'),
//                Forms\Components\TextInput::make('number_user_tagged_by'),
//                Forms\Components\TextInput::make('number_of_user_joined_variables'),
//                Forms\Components\TextInput::make('number_of_user_ingredients'),
//                Forms\Components\TextInput::make('number_of_user_foods'),
//                Forms\Components\TextInput::make('number_of_user_children'),
//                Forms\Components\TextInput::make('number_of_user_parents'),
//                Forms\Components\Toggle::make('is_public'),
//                Forms\Components\Toggle::make('is_goal'),
//                Forms\Components\Toggle::make('controllable'),
//                Forms\Components\Toggle::make('boring'),
//                Forms\Components\TextInput::make('slug')
//                    ->maxLength(200),
//                Forms\Components\Toggle::make('predictor'),
            ]);
    }
	/**
	 * @throws Exception
	 */
	public static function table(Table $table): Table
    {
        return $table
            ->columns([
//                Tables\Columns\TextColumn::make('client.client_id'),
//                Tables\Columns\TextColumn::make('user.ID'),
                Tables\Columns\TextColumn::make('variable.name'),
                Tables\Columns\TextColumn::make('default_unit.name'),
                Tables\Columns\TextColumn::make('variable_category.name'),
//                Tables\Columns\TextColumn::make('last_unit.name'),
//                Tables\Columns\TextColumn::make('wp_post.ID'),
//                Tables\Columns\TextColumn::make('best_user_variable_relationship.id'),
//                Tables\Columns\TextColumn::make('parent_id'),
//                Tables\Columns\TextColumn::make('minimum_allowed_value'),
//                Tables\Columns\TextColumn::make('maximum_allowed_value'),
//                Tables\Columns\TextColumn::make('filling_value'),
//                Tables\Columns\TextColumn::make('join_with'),
//                Tables\Columns\TextColumn::make('onset_delay'),
//                Tables\Columns\TextColumn::make('duration_of_action'),
//                Tables\Columns\IconColumn::make('cause_only')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('filling_type'),
                Tables\Columns\TextColumn::make('number_of_processed_daily_measurements'),
//                Tables\Columns\TextColumn::make('measurements_at_last_analysis'),
//                Tables\Columns\TextColumn::make('last_original_unit_id'),
                Tables\Columns\TextColumn::make('last_value'),
//                Tables\Columns\TextColumn::make('last_original_value'),
//                Tables\Columns\TextColumn::make('number_of_correlations'),
//                Tables\Columns\TextColumn::make('status'),
//                Tables\Columns\TextColumn::make('standard_deviation'),
//                Tables\Columns\TextColumn::make('variance'),
//                Tables\Columns\TextColumn::make('minimum_recorded_value'),
//                Tables\Columns\TextColumn::make('maximum_recorded_value'),
//                Tables\Columns\TextColumn::make('mean'),
//                Tables\Columns\TextColumn::make('median'),
//                Tables\Columns\TextColumn::make('most_common_original_unit_id'),
//                Tables\Columns\TextColumn::make('most_common_value'),
//                Tables\Columns\TextColumn::make('number_of_unique_daily_values'),
//                Tables\Columns\TextColumn::make('number_of_unique_values'),
//                Tables\Columns\TextColumn::make('number_of_changes'),
//                Tables\Columns\TextColumn::make('skewness'),
//                Tables\Columns\TextColumn::make('kurtosis'),
//                Tables\Columns\TextColumn::make('latitude'),
//                Tables\Columns\TextColumn::make('longitude'),
//                Tables\Columns\TextColumn::make('location'),
//                Tables\Columns\TextColumn::make('created_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('updated_at')
//                    ->dateTime(),
//                Tables\Columns\IconColumn::make('outcome')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('data_sources_count'),
//                Tables\Columns\TextColumn::make('earliest_filling_time'),
//                Tables\Columns\TextColumn::make('latest_filling_time'),
//                Tables\Columns\TextColumn::make('last_processed_daily_value'),
//                Tables\Columns\IconColumn::make('outcome_of_interest')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('predictor_of_interest')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('experiment_start_time')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('experiment_end_time')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('description'),
//                Tables\Columns\TextColumn::make('alias'),
//                Tables\Columns\TextColumn::make('deleted_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('second_to_last_value'),
//                Tables\Columns\TextColumn::make('third_to_last_value'),
//                Tables\Columns\TextColumn::make('number_of_user_variable_relationships_as_effect'),
//                Tables\Columns\TextColumn::make('number_of_user_variable_relationships_as_cause'),
//                Tables\Columns\TextColumn::make('combination_operation'),
//                Tables\Columns\TextColumn::make('informational_url'),
//                Tables\Columns\TextColumn::make('most_common_connector.name'),
//                Tables\Columns\TextColumn::make('valence'),
//                Tables\Columns\TextColumn::make('wikipedia_title'),
//                Tables\Columns\TextColumn::make('number_of_tracking_reminders'),
                Tables\Columns\TextColumn::make('number_of_raw_measurements_with_tags_joins_children'),
//                Tables\Columns\TextColumn::make('most_common_source_name'),
                Tables\Columns\TextColumn::make('optimal_value_message'),
//                Tables\Columns\TextColumn::make('best_cause_variable.name'),
//                Tables\Columns\TextColumn::make('best_effect_variable.name'),
//                Tables\Columns\TextColumn::make('user_maximum_allowed_daily_value'),
//                Tables\Columns\TextColumn::make('user_minimum_allowed_daily_value'),
//                Tables\Columns\TextColumn::make('user_minimum_allowed_non_zero_value'),
//                Tables\Columns\TextColumn::make('minimum_allowed_seconds_between_measurements'),
//                Tables\Columns\TextColumn::make('average_seconds_between_measurements'),
//                Tables\Columns\TextColumn::make('median_seconds_between_measurements'),
//                Tables\Columns\TextColumn::make('last_correlated_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('number_of_measurements_with_tags_at_last_correlation'),
//                Tables\Columns\TextColumn::make('analysis_settings_modified_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('newest_data_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('analysis_requested_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('reason_for_analysis'),
//                Tables\Columns\TextColumn::make('analysis_started_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('analysis_ended_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('user_error_message'),
//                Tables\Columns\TextColumn::make('internal_error_message'),
//                Tables\Columns\TextColumn::make('earliest_source_measurement_start_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('latest_source_measurement_start_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('latest_tagged_measurement_start_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('earliest_tagged_measurement_start_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('latest_non_tagged_measurement_start_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('earliest_non_tagged_measurement_start_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('number_of_soft_deleted_measurements'),
//                Tables\Columns\TextColumn::make('number_of_measurements'),
//                Tables\Columns\TextColumn::make('number_of_tracking_reminder_notifications'),
//                Tables\Columns\TextColumn::make('deletion_reason'),
//                Tables\Columns\TextColumn::make('record_size_in_kb'),
//                Tables\Columns\TextColumn::make('number_of_common_tags'),
//                Tables\Columns\TextColumn::make('number_common_tagged_by'),
//                Tables\Columns\TextColumn::make('number_of_common_joined_variables'),
//                Tables\Columns\TextColumn::make('number_of_common_ingredients'),
//                Tables\Columns\TextColumn::make('number_of_common_foods'),
//                Tables\Columns\TextColumn::make('number_of_common_children'),
//                Tables\Columns\TextColumn::make('number_of_common_parents'),
//                Tables\Columns\TextColumn::make('number_of_user_tags'),
//                Tables\Columns\TextColumn::make('number_user_tagged_by'),
//                Tables\Columns\TextColumn::make('number_of_user_joined_variables'),
//                Tables\Columns\TextColumn::make('number_of_user_ingredients'),
//                Tables\Columns\TextColumn::make('number_of_user_foods'),
//                Tables\Columns\TextColumn::make('number_of_user_children'),
//                Tables\Columns\TextColumn::make('number_of_user_parents'),
//                Tables\Columns\IconColumn::make('is_public')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('is_goal')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('controllable')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('boring')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('slug'),
//                Tables\Columns\IconColumn::make('predictor')
//                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUserVariables::route('/'),
            'create' => Pages\CreateUserVariable::route('/create'),
            'edit' => Pages\EditUserVariable::route('/{record}/edit'),
        ];
    }    
}
