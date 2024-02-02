<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConnectorRequestResource\Pages;
use App\Filament\Resources\ConnectorRequestResource\RelationManagers;
use App\Models\ConnectorRequest;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConnectorRequestResource extends Resource
{
    protected static ?string $model = ConnectorRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('connector_id')
                    ->relationship('connector', 'name')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID')
                    ->required(),
                Forms\Components\Select::make('connection_id')
                    ->relationship('connection', 'id'),
                Forms\Components\Select::make('connector_import_id')
                    ->relationship('connector_import', 'id')
                    ->required(),
                Forms\Components\TextInput::make('method')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('code')
                    ->required(),
                Forms\Components\TextInput::make('uri')
                    ->required()
                    ->maxLength(2083),
                Forms\Components\Textarea::make('response_body')
                    ->maxLength(16777215),
                Forms\Components\Textarea::make('request_body')
                    ->maxLength(65535),
                Forms\Components\Textarea::make('request_headers')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\TextInput::make('content_type')
                    ->maxLength(100),
                Forms\Components\DateTimePicker::make('imported_data_from_at'),
            ]);
    }
	/**
	 * @throws Exception
	 */
	public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('connector.name'),
                Tables\Columns\TextColumn::make('user.ID'),
                Tables\Columns\TextColumn::make('connection.id'),
                Tables\Columns\TextColumn::make('connector_import.id'),
                Tables\Columns\TextColumn::make('method'),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('uri'),
                Tables\Columns\TextColumn::make('response_body'),
                Tables\Columns\TextColumn::make('request_body'),
                Tables\Columns\TextColumn::make('request_headers'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('content_type'),
                Tables\Columns\TextColumn::make('imported_data_from_at')
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
            'index' => Pages\ListConnectorRequests::route('/'),
            'view' => Pages\ViewConnectorRequest::route('/{record}'),
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
