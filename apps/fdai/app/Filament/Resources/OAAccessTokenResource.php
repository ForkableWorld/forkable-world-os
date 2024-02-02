<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OAAccessTokenResource\Pages;
use App\Filament\Resources\OAAccessTokenResource\RelationManagers;
use App\Models\OAAccessToken;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OAAccessTokenResource extends Resource
{
    protected static ?string $model = OAAccessToken::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id'),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID'),
                Forms\Components\DateTimePicker::make('expires'),
                Forms\Components\TextInput::make('scope')
                    ->maxLength(2000),
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
                Tables\Columns\TextColumn::make('expires')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('scope'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('created_at')
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
            'index' => Pages\ListOAAccessTokens::route('/'),
            'create' => Pages\CreateOAAccessToken::route('/create'),
            'view' => Pages\ViewOAAccessToken::route('/{record}'),
            'edit' => Pages\EditOAAccessToken::route('/{record}/edit'),
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
