<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeasurementResource\Pages;
use App\Filament\Resources\MeasurementResource\RelationManagers;
use App\Models\Measurement;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MeasurementResource extends Resource
{
    protected static ?string $model = Measurement::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID')
                    ->required(),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id')
                    ->required(),
                Forms\Components\Select::make('connector_id')
                    ->relationship('connector', 'name'),
                Forms\Components\Select::make('variable_id')
                    ->relationship('variable', 'name')
                    ->required(),
                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'name')
                    ->required(),
                Forms\Components\Select::make('original_unit_id')
                    ->relationship('original_unit', 'name')
                    ->required(),
                Forms\Components\Select::make('variable_category_id')
                    ->relationship('variable_category', 'name')
                    ->required(),
                Forms\Components\Select::make('user_variable_id')
                    ->relationship('user_variable', 'id')
                    ->required(),
                Forms\Components\Select::make('connection_id')
                    ->relationship('connection', 'id'),
                Forms\Components\Select::make('connector_import_id')
                    ->relationship('connector_import', 'id'),
                Forms\Components\TextInput::make('start_time')
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->required(),
                Forms\Components\TextInput::make('original_value')
                    ->required(),
                Forms\Components\TextInput::make('duration'),
                Forms\Components\Textarea::make('note')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('latitude'),
                Forms\Components\TextInput::make('longitude'),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255),
                Forms\Components\Textarea::make('error')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('source_name')
                    ->maxLength(80),
                Forms\Components\DateTimePicker::make('start_at')
                    ->required(),
                Forms\Components\TextInput::make('deletion_reason')
                    ->maxLength(280),
                Forms\Components\DateTimePicker::make('original_start_at')
                    ->required(),
            ]);
    }
	/**
	 * @throws Exception
	 */
	public static function table(Table $table): Table
    {
        return $table
            ->columns([
	                      Tables\Columns\TextColumn::make('start_at')->dateTime(),
                Tables\Columns\TextColumn::make('variable.name'),
                Tables\Columns\TextColumn::make('user.ID'),
//                Tables\Columns\TextColumn::make('client.client_id'),
                Tables\Columns\TextColumn::make('connector.name'),
                Tables\Columns\TextColumn::make('unit.name'),
//                Tables\Columns\TextColumn::make('original_unit.name'),
                Tables\Columns\TextColumn::make('variable_category.name'),
//                Tables\Columns\TextColumn::make('user_variable.id'),
//                Tables\Columns\TextColumn::make('connection.id'),
//                Tables\Columns\TextColumn::make('connector_import.id'),
//                Tables\Columns\TextColumn::make('start_time'),
//                Tables\Columns\TextColumn::make('value'),
//                Tables\Columns\TextColumn::make('original_value'),
//                Tables\Columns\TextColumn::make('duration'),
//                Tables\Columns\TextColumn::make('note'),
//                Tables\Columns\TextColumn::make('latitude'),
//                Tables\Columns\TextColumn::make('longitude'),
//                Tables\Columns\TextColumn::make('location'),
//                Tables\Columns\TextColumn::make('created_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('updated_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('error'),
//                Tables\Columns\TextColumn::make('deleted_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('source_name'),
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('deletion_reason'),
//                Tables\Columns\TextColumn::make('original_start_at')
//                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
	        ->defaultSort(Measurement::FIELD_START_AT, 'desc');
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
            'index' => Pages\ListMeasurements::route('/'),
            'create' => Pages\CreateMeasurement::route('/create'),
            'edit' => Pages\EditMeasurement::route('/{record}/edit'),
        ];
    }    
}
