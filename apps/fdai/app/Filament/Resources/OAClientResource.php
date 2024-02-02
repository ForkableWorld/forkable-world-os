<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OAClientResource\Pages;
use App\Filament\Resources\OAClientResource\RelationManagers;
use App\Models\OAClient;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OAClientResource extends Resource
{
    protected static ?string $model = OAClient::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID')
                    ->required(),
                Forms\Components\TextInput::make('client_secret')
                    ->required()
                    ->maxLength(80),
                Forms\Components\TextInput::make('redirect_uri')
                    ->maxLength(2000),
                Forms\Components\TextInput::make('grant_types')
                    ->maxLength(80),
                Forms\Components\TextInput::make('icon_url')
                    ->maxLength(2083),
                Forms\Components\TextInput::make('app_identifier')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('earliest_measurement_start_at'),
                Forms\Components\DateTimePicker::make('latest_measurement_start_at'),
                Forms\Components\TextInput::make('number_of_global_variable_relationships'),
                Forms\Components\TextInput::make('number_of_applications'),
                Forms\Components\TextInput::make('number_of_oauth_access_tokens'),
                Forms\Components\TextInput::make('number_of_oauth_authorization_codes'),
                Forms\Components\TextInput::make('number_of_oauth_refresh_tokens'),
                Forms\Components\TextInput::make('number_of_button_clicks'),
                Forms\Components\TextInput::make('number_of_collaborators'),
                Forms\Components\TextInput::make('number_of_common_tags'),
                Forms\Components\TextInput::make('number_of_connections'),
                Forms\Components\TextInput::make('number_of_connector_imports'),
                Forms\Components\TextInput::make('number_of_connectors'),
                Forms\Components\TextInput::make('number_of_correlations'),
                Forms\Components\TextInput::make('number_of_measurement_exports'),
                Forms\Components\TextInput::make('number_of_measurement_imports'),
                Forms\Components\TextInput::make('number_of_measurements'),
                Forms\Components\TextInput::make('number_of_sent_emails')
                    ->email(),
                Forms\Components\TextInput::make('number_of_studies'),
                Forms\Components\TextInput::make('number_of_tracking_reminder_notifications'),
                Forms\Components\TextInput::make('number_of_tracking_reminders'),
                Forms\Components\TextInput::make('number_of_user_tags'),
                Forms\Components\TextInput::make('number_of_user_variables'),
                Forms\Components\TextInput::make('number_of_variables'),
                Forms\Components\TextInput::make('number_of_votes'),
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
                Tables\Columns\TextColumn::make('user.ID'),
                Tables\Columns\TextColumn::make('client_secret'),
                Tables\Columns\TextColumn::make('redirect_uri'),
                Tables\Columns\TextColumn::make('grant_types'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('icon_url'),
                Tables\Columns\TextColumn::make('app_identifier'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('earliest_measurement_start_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('latest_measurement_start_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('number_of_global_variable_relationships'),
                Tables\Columns\TextColumn::make('number_of_applications'),
                Tables\Columns\TextColumn::make('number_of_oauth_access_tokens'),
                Tables\Columns\TextColumn::make('number_of_oauth_authorization_codes'),
                Tables\Columns\TextColumn::make('number_of_oauth_refresh_tokens'),
                Tables\Columns\TextColumn::make('number_of_button_clicks'),
                Tables\Columns\TextColumn::make('number_of_collaborators'),
                Tables\Columns\TextColumn::make('number_of_common_tags'),
                Tables\Columns\TextColumn::make('number_of_connections'),
                Tables\Columns\TextColumn::make('number_of_connector_imports'),
                Tables\Columns\TextColumn::make('number_of_connectors'),
                Tables\Columns\TextColumn::make('number_of_correlations'),
                Tables\Columns\TextColumn::make('number_of_measurement_exports'),
                Tables\Columns\TextColumn::make('number_of_measurement_imports'),
                Tables\Columns\TextColumn::make('number_of_measurements'),
                Tables\Columns\TextColumn::make('number_of_sent_emails'),
                Tables\Columns\TextColumn::make('number_of_studies'),
                Tables\Columns\TextColumn::make('number_of_tracking_reminder_notifications'),
                Tables\Columns\TextColumn::make('number_of_tracking_reminders'),
                Tables\Columns\TextColumn::make('number_of_user_tags'),
                Tables\Columns\TextColumn::make('number_of_user_variables'),
                Tables\Columns\TextColumn::make('number_of_variables'),
                Tables\Columns\TextColumn::make('number_of_votes'),
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
            'index' => Pages\ListOAClients::route('/'),
            'create' => Pages\CreateOAClient::route('/create'),
            'view' => Pages\ViewOAClient::route('/{record}'),
            'edit' => Pages\EditOAClient::route('/{record}/edit'),
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
