<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConnectorImportResource\Pages;
use App\Filament\Resources\ConnectorImportResource\RelationManagers;
use App\Models\ConnectorImport;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConnectorImportResource extends Resource
{
    protected static ?string $model = ConnectorImport::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id'),
                Forms\Components\Select::make('connection_id')
                    ->relationship('connection', 'id'),
                Forms\Components\Select::make('connector_id')
                    ->relationship('connector', 'name')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID')
                    ->required(),
                Forms\Components\DateTimePicker::make('earliest_measurement_at'),
                Forms\Components\DateTimePicker::make('import_ended_at'),
                Forms\Components\DateTimePicker::make('import_started_at'),
                Forms\Components\Textarea::make('internal_error_message')
                    ->maxLength(65535),
                Forms\Components\DateTimePicker::make('latest_measurement_at'),
                Forms\Components\TextInput::make('number_of_measurements')
                    ->required(),
                Forms\Components\TextInput::make('reason_for_import')
                    ->maxLength(255),
                Forms\Components\Toggle::make('success'),
                Forms\Components\Textarea::make('user_error_message')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('additional_meta_data'),
                Forms\Components\TextInput::make('number_of_connector_requests'),
                Forms\Components\DateTimePicker::make('imported_data_from_at'),
                Forms\Components\DateTimePicker::make('imported_data_end_at'),
                Forms\Components\Textarea::make('credentials')
                    ->maxLength(65535),
                Forms\Components\DateTimePicker::make('connector_requests'),
            ]);
    }
	/**
	 * @throws Exception
	 */
	public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.client_id'),
                Tables\Columns\TextColumn::make('connection.id'),
                Tables\Columns\TextColumn::make('connector.name'),
                Tables\Columns\TextColumn::make('user.ID'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('earliest_measurement_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('import_ended_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('import_started_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('internal_error_message'),
                Tables\Columns\TextColumn::make('latest_measurement_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('number_of_measurements'),
                Tables\Columns\TextColumn::make('reason_for_import'),
                Tables\Columns\IconColumn::make('success')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('user_error_message'),
                Tables\Columns\TextColumn::make('additional_meta_data'),
                Tables\Columns\TextColumn::make('number_of_connector_requests'),
                Tables\Columns\TextColumn::make('imported_data_from_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('imported_data_end_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('credentials'),
                Tables\Columns\TextColumn::make('connector_requests')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListConnectorImports::route('/'),
            'view' => Pages\ViewConnectorImport::route('/{record}'),
        ];
    }    
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
