<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
//                Forms\Components\TextInput::make('code')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('descriptive_name')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('code_system')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('definition')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('synonym')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('status')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('kind_of_quantity')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('concept_id')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('dimension')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('name')
//                    ->required()
//                    ->maxLength(64),
//                Forms\Components\TextInput::make('abbreviated_name')
//                    ->maxLength(40),
//                Forms\Components\Select::make('unit_category_id')
//                    ->relationship('unit_category', 'name')
//                    ->required(),
//                Forms\Components\TextInput::make('minimum_value'),
//                Forms\Components\TextInput::make('maximum_value'),
//                Forms\Components\TextInput::make('filling_type')
//                    ->required(),
//                Forms\Components\TextInput::make('number_of_outcome_population_studies'),
//                Forms\Components\TextInput::make('number_of_common_tags_where_tag_variable_unit'),
//                Forms\Components\TextInput::make('number_of_common_tags_where_tagged_variable_unit'),
//                Forms\Components\TextInput::make('number_of_outcome_case_studies'),
//                Forms\Components\TextInput::make('number_of_measurements'),
//                Forms\Components\TextInput::make('number_of_user_variables_where_default_unit'),
//                Forms\Components\TextInput::make('number_of_variable_categories_where_default_unit'),
//                Forms\Components\TextInput::make('number_of_variables_where_default_unit'),
//                Forms\Components\Toggle::make('advanced')
//                    ->required(),
//                Forms\Components\Toggle::make('manual_tracking')
//                    ->required(),
//                Forms\Components\TextInput::make('filling_value'),
//                Forms\Components\TextInput::make('scale')
//                    ->required(),
//                Forms\Components\Textarea::make('conversion_steps')
//                    ->maxLength(65535),
//                Forms\Components\TextInput::make('maximum_daily_value'),
//                Forms\Components\TextInput::make('sort_order')
//                    ->required(),
//                Forms\Components\TextInput::make('slug')
//                    ->maxLength(200),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
//                Tables\Columns\TextColumn::make('code'),
//                Tables\Columns\TextColumn::make('descriptive_name'),
//                Tables\Columns\TextColumn::make('code_system'),
//                Tables\Columns\TextColumn::make('definition'),
//                Tables\Columns\TextColumn::make('synonym'),
//                Tables\Columns\TextColumn::make('status'),
//                Tables\Columns\TextColumn::make('kind_of_quantity'),
//                Tables\Columns\TextColumn::make('concept_id'),
//                Tables\Columns\TextColumn::make('dimension'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('abbreviated_name'),
                Tables\Columns\TextColumn::make('unit_category.name'),
                Tables\Columns\TextColumn::make('minimum_value'),
                Tables\Columns\TextColumn::make('maximum_value'),
//                Tables\Columns\TextColumn::make('created_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('updated_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('deleted_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('filling_type'),
//                Tables\Columns\TextColumn::make('number_of_outcome_population_studies'),
//                Tables\Columns\TextColumn::make('number_of_common_tags_where_tag_variable_unit'),
//                Tables\Columns\TextColumn::make('number_of_common_tags_where_tagged_variable_unit'),
//                Tables\Columns\TextColumn::make('number_of_outcome_case_studies'),
//                Tables\Columns\TextColumn::make('number_of_measurements'),
//                Tables\Columns\TextColumn::make('number_of_user_variables_where_default_unit'),
//                Tables\Columns\TextColumn::make('number_of_variable_categories_where_default_unit'),
//                Tables\Columns\TextColumn::make('number_of_variables_where_default_unit'),
                Tables\Columns\IconColumn::make('advanced')->boolean(),
//                Tables\Columns\IconColumn::make('manual_tracking')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('filling_value'),
//                Tables\Columns\TextColumn::make('scale'),
//                Tables\Columns\TextColumn::make('conversion_steps'),
//                Tables\Columns\TextColumn::make('maximum_daily_value'),
//                Tables\Columns\TextColumn::make('sort_order'),
//                Tables\Columns\TextColumn::make('slug'),
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
            'index' => Pages\ListUnits::route('/'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }    
}
