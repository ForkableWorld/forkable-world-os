<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VariableCategoryResource\Pages;
use App\Filament\Resources\VariableCategoryResource\RelationManagers;
use App\Models\VariableCategory;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VariableCategoryResource extends Resource
{
    protected static ?string $model = VariableCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('default_unit_id')
                    ->relationship('default_unit', 'name'),
                Forms\Components\Select::make('wp_post_id')
                    ->relationship('wp_post', 'ID'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(64),
                Forms\Components\TextInput::make('filling_value'),
                Forms\Components\TextInput::make('maximum_allowed_value'),
                Forms\Components\TextInput::make('minimum_allowed_value'),
                Forms\Components\TextInput::make('duration_of_action')
                    ->required(),
                Forms\Components\TextInput::make('onset_delay')
                    ->required(),
                Forms\Components\TextInput::make('combination_operation')
                    ->required(),
                Forms\Components\Toggle::make('cause_only')
                    ->required(),
                Forms\Components\Toggle::make('outcome'),
                Forms\Components\Textarea::make('image_url')
                    ->maxLength(255),
                Forms\Components\Toggle::make('manual_tracking'),
                Forms\Components\TextInput::make('minimum_allowed_seconds_between_measurements'),
                Forms\Components\TextInput::make('average_seconds_between_measurements'),
                Forms\Components\TextInput::make('median_seconds_between_measurements'),
                Forms\Components\TextInput::make('filling_type'),
                Forms\Components\TextInput::make('number_of_outcome_population_studies'),
                Forms\Components\TextInput::make('number_of_predictor_population_studies'),
                Forms\Components\TextInput::make('number_of_outcome_case_studies'),
                Forms\Components\TextInput::make('number_of_predictor_case_studies'),
                Forms\Components\TextInput::make('number_of_measurements'),
                Forms\Components\TextInput::make('number_of_user_variables'),
                Forms\Components\TextInput::make('number_of_variables'),
                Forms\Components\Toggle::make('is_public'),
                Forms\Components\TextInput::make('synonyms')
                    ->required()
                    ->maxLength(600),
                Forms\Components\TextInput::make('amazon_product_category')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Toggle::make('boring'),
                Forms\Components\Toggle::make('effect_only'),
                Forms\Components\Toggle::make('predictor'),
                Forms\Components\TextInput::make('font_awesome')
                    ->maxLength(100),
                Forms\Components\TextInput::make('ion_icon')
                    ->maxLength(100),
                Forms\Components\TextInput::make('more_info')
                    ->maxLength(255),
                Forms\Components\TextInput::make('valence')
                    ->required(),
                Forms\Components\TextInput::make('name_singular')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->required(),
                Forms\Components\TextInput::make('is_goal')
                    ->required(),
                Forms\Components\TextInput::make('controllable')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->maxLength(200),
                Forms\Components\TextInput::make('string_id')
                    ->maxLength(64),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
//                Tables\Columns\TextColumn::make('wp_post.ID'),
	                      Tables\Columns\ImageColumn::make('image_url')->label("Image"),
                Tables\Columns\TextColumn::make('name'),
	                      Tables\Columns\TextColumn::make('default_unit.name'),
//                Tables\Columns\TextColumn::make('filling_value'),
//                Tables\Columns\TextColumn::make('maximum_allowed_value'),
//                Tables\Columns\TextColumn::make('minimum_allowed_value'),
//                Tables\Columns\TextColumn::make('duration_of_action'),
//                Tables\Columns\TextColumn::make('onset_delay'),
//                Tables\Columns\TextColumn::make('combination_operation'),
//                Tables\Columns\IconColumn::make('cause_only')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('outcome')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('created_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('updated_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('deleted_at')
//                    ->dateTime(),
//                Tables\Columns\IconColumn::make('manual_tracking')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('minimum_allowed_seconds_between_measurements'),
//                Tables\Columns\TextColumn::make('average_seconds_between_measurements'),
//                Tables\Columns\TextColumn::make('median_seconds_between_measurements'),
//                Tables\Columns\TextColumn::make('filling_type'),
//                Tables\Columns\TextColumn::make('number_of_outcome_population_studies'),
//                Tables\Columns\TextColumn::make('number_of_predictor_population_studies'),
//                Tables\Columns\TextColumn::make('number_of_outcome_case_studies'),
//                Tables\Columns\TextColumn::make('number_of_predictor_case_studies'),
//                Tables\Columns\TextColumn::make('number_of_measurements'),
//                Tables\Columns\TextColumn::make('number_of_user_variables'),
                Tables\Columns\TextColumn::make('number_of_variables'),
//                Tables\Columns\IconColumn::make('is_public')
//                    ->boolean(),
                Tables\Columns\TextColumn::make('synonyms'),
//                Tables\Columns\TextColumn::make('amazon_product_category'),
//                Tables\Columns\IconColumn::make('boring')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('effect_only')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('predictor')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('font_awesome'),
//                Tables\Columns\TextColumn::make('ion_icon'),
//                Tables\Columns\TextColumn::make('more_info'),
//                Tables\Columns\TextColumn::make('valence'),
//                Tables\Columns\TextColumn::make('name_singular'),
//                Tables\Columns\TextColumn::make('sort_order'),
//                Tables\Columns\TextColumn::make('is_goal'),
//                Tables\Columns\TextColumn::make('controllable'),
//                Tables\Columns\TextColumn::make('slug'),
//                Tables\Columns\TextColumn::make('string_id'),
//                Tables\Columns\TextColumn::make('description'),
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
            'index' => Pages\ListVariableCategories::route('/'),
            'create' => Pages\CreateVariableCategory::route('/create'),
            'edit' => Pages\EditVariableCategory::route('/{record}/edit'),
        ];
    }    
}
