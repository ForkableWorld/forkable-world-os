<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConnectorResource\Pages;
use App\Filament\Resources\ConnectorResource\RelationManagers;
use App\Models\Connector;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConnectorResource extends Resource
{
    protected static ?string $model = Connector::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id'),
                Forms\Components\Select::make('wp_post_id')
                    ->relationship('wp_post', 'ID'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(30),
                Forms\Components\TextInput::make('display_name')
                    ->required()
                    ->maxLength(30),
                Forms\Components\TextInput::make('image')
                    ->required()
                    ->maxLength(2083),
                Forms\Components\TextInput::make('get_it_url')
                    ->maxLength(2083),
                Forms\Components\Textarea::make('short_description')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\Textarea::make('long_description')
                    ->required(),
                Forms\Components\Toggle::make('enabled')
                    ->required(),
                Forms\Components\Toggle::make('oauth')
                    ->required(),
                Forms\Components\Toggle::make('qm_client'),
                Forms\Components\TextInput::make('number_of_connections'),
                Forms\Components\TextInput::make('number_of_connector_imports'),
                Forms\Components\TextInput::make('number_of_connector_requests'),
                Forms\Components\TextInput::make('number_of_measurements'),
                Forms\Components\Toggle::make('is_public'),
                Forms\Components\TextInput::make('sort_order')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->maxLength(200),
                Forms\Components\TextInput::make('available_outside_us')
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
                Tables\Columns\TextColumn::make('client.client_id'),
                Tables\Columns\TextColumn::make('wp_post.ID'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('display_name'),
                Tables\Columns\TextColumn::make('image'),
                Tables\Columns\TextColumn::make('get_it_url'),
                Tables\Columns\TextColumn::make('short_description'),
                Tables\Columns\TextColumn::make('long_description'),
                Tables\Columns\IconColumn::make('enabled')
                    ->boolean(),
                Tables\Columns\IconColumn::make('oauth')
                    ->boolean(),
                Tables\Columns\IconColumn::make('qm_client')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('number_of_connections'),
                Tables\Columns\TextColumn::make('number_of_connector_imports'),
                Tables\Columns\TextColumn::make('number_of_connector_requests'),
                Tables\Columns\TextColumn::make('number_of_measurements'),
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order'),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('available_outside_us'),
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
            'index' => Pages\ListConnectors::route('/'),
            'create' => Pages\CreateConnector::route('/create'),
            'view' => Pages\ViewConnector::route('/{record}'),
            'edit' => Pages\EditConnector::route('/{record}/edit'),
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
