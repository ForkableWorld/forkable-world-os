<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CredentialResource\Pages;
use App\Filament\Resources\CredentialResource\RelationManagers;
use App\Models\Credential;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CredentialResource extends Resource
{
    protected static ?string $model = Credential::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID')
                    ->required(),
                Forms\Components\Select::make('connector_id')
                    ->relationship('connector', 'name')
                    ->required(),
                Forms\Components\TextInput::make('attr_key')
                    ->required()
                    ->maxLength(16),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id'),
                Forms\Components\TextInput::make('attr_value')
                    ->required()
                    ->maxLength(3000),
                Forms\Components\TextInput::make('status')
                    ->maxLength(32),
                Forms\Components\Textarea::make('message')
                    ->maxLength(16777215),
                Forms\Components\DateTimePicker::make('expires_at'),
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
                Tables\Columns\TextColumn::make('connector.name'),
                Tables\Columns\TextColumn::make('attr_key'),
                Tables\Columns\TextColumn::make('client.client_id'),
                Tables\Columns\TextColumn::make('attr_value'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('message'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('deleted_at')
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
            'index' => Pages\ListCredentials::route('/'),
            'view' => Pages\ViewCredential::route('/{record}'),
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
