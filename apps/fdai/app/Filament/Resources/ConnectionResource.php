<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConnectionResource\Pages;
use App\Filament\Resources\ConnectionResource\RelationManagers;
use App\Models\Connection;
use App\Models\Measurement;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConnectionResource extends Resource
{
    protected static ?string $model = Connection::class;

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
//                Forms\Components\Select::make('connector_id')
//                    ->relationship('connector', 'name')
//                    ->required(),
//                Forms\Components\Select::make('wp_post_id')
//                    ->relationship('wp_post', 'ID'),
//                Forms\Components\TextInput::make('connect_status')
//                    ->required()
//                    ->maxLength(32),
//                Forms\Components\Textarea::make('connect_error')
//                    ->maxLength(65535),
//                Forms\Components\DateTimePicker::make('update_requested_at'),
//                Forms\Components\TextInput::make('update_status')
//                    ->required()
//                    ->maxLength(32),
//                Forms\Components\Textarea::make('update_error')
//                    ->maxLength(65535),
//                Forms\Components\DateTimePicker::make('last_successful_updated_at'),
//                Forms\Components\TextInput::make('total_measurements_in_last_update'),
//                Forms\Components\TextInput::make('user_message')
//                    ->maxLength(255),
//                Forms\Components\DateTimePicker::make('latest_measurement_at'),
//                Forms\Components\DateTimePicker::make('import_started_at'),
//                Forms\Components\DateTimePicker::make('import_ended_at'),
//                Forms\Components\TextInput::make('reason_for_import')
//                    ->maxLength(255),
//                Forms\Components\Textarea::make('user_error_message')
//                    ->maxLength(65535),
//                Forms\Components\Textarea::make('internal_error_message')
//                    ->maxLength(65535),
//                Forms\Components\TextInput::make('number_of_connector_imports'),
//                Forms\Components\TextInput::make('number_of_connector_requests'),
//                Forms\Components\Textarea::make('credentials')
//                    ->maxLength(65535),
//                Forms\Components\DateTimePicker::make('imported_data_from_at'),
//                Forms\Components\DateTimePicker::make('imported_data_end_at'),
//                Forms\Components\TextInput::make('number_of_measurements'),
//                Forms\Components\Toggle::make('is_public'),
//                Forms\Components\TextInput::make('slug')
//                    ->maxLength(200),
//                Forms\Components\Textarea::make('meta')
//                    ->maxLength(65535),
//                Forms\Components\TextInput::make('connector_user_id')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('connector_user_email')
//                    ->email()
//                    ->maxLength(320),
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
                Tables\Columns\TextColumn::make('user.ID'),
                Tables\Columns\TextColumn::make('connector.name'),
//                Tables\Columns\TextColumn::make('wp_post.ID'),
                Tables\Columns\TextColumn::make('connect_status'),
                      Tables\Columns\TextColumn::make('latest_measurement_at')->dateTime(),
	                      Tables\Columns\TextColumn::make('total_measurements_in_last_update')
	                                               ->label("New Measurements"),
                      Tables\Columns\TextColumn::make('import_started_at')->dateTime()->label("Import Started"),
                      Tables\Columns\TextColumn::make('import_ended_at')->dateTime()->label("Import Ended"),
//                Tables\Columns\TextColumn::make('connect_error'),
                Tables\Columns\TextColumn::make('update_requested_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('update_status'),
                Tables\Columns\TextColumn::make('update_error'),
                Tables\Columns\TextColumn::make('last_successful_updated_at')
                    ->dateTime(),
//                Tables\Columns\TextColumn::make('created_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('updated_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('deleted_at')
//                    ->dateTime(),
                Tables\Columns\TextColumn::make('user_message'),
                Tables\Columns\TextColumn::make('reason_for_import'),
                Tables\Columns\TextColumn::make('user_error_message'),
                Tables\Columns\TextColumn::make('internal_error_message'),
                Tables\Columns\TextColumn::make('number_of_connector_imports'),
                Tables\Columns\TextColumn::make('number_of_connector_requests'),
//                Tables\Columns\TextColumn::make('credentials'),
                Tables\Columns\TextColumn::make('imported_data_from_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('imported_data_end_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('number_of_measurements'),
//                Tables\Columns\IconColumn::make('is_public')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('slug'),
//                Tables\Columns\TextColumn::make('meta'),
                Tables\Columns\TextColumn::make('connector_user_id'),
                Tables\Columns\TextColumn::make('connector_user_email'),
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
            ])
	        ->defaultSort(Connection::FIELD_IMPORT_STARTED_AT, 'desc');
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
            'index' => Pages\ListConnections::route('/'),
            'view' => Pages\ViewConnection::route('/{record}'),
            'edit' => Pages\EditConnection::route('/{record}/edit'),
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
