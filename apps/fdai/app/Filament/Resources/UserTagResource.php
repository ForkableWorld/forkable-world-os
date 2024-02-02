<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserTagResource\Pages;
use App\Filament\Resources\UserTagResource\RelationManagers;
use App\Models\UserTag;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserTagResource extends Resource
{
    protected static ?string $model = UserTag::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tagged_variable_id')
                    ->relationship('tagged_variable', 'name')
                    ->required(),
                Forms\Components\Select::make('tag_variable_id')
                    ->relationship('tag_variable', 'name')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID')
                    ->required(),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id'),
                Forms\Components\Select::make('tagged_user_variable_id')
                    ->relationship('tagged_user_variable', 'id'),
                Forms\Components\Select::make('tag_user_variable_id')
                    ->relationship('tag_user_variable', 'id'),
                Forms\Components\TextInput::make('conversion_factor')
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
                Tables\Columns\TextColumn::make('tagged_variable.name'),
                Tables\Columns\TextColumn::make('tag_variable.name'),
                Tables\Columns\TextColumn::make('user.ID'),
                Tables\Columns\TextColumn::make('client.client_id'),
                Tables\Columns\TextColumn::make('tagged_user_variable.id'),
                Tables\Columns\TextColumn::make('tag_user_variable.id'),
                Tables\Columns\TextColumn::make('conversion_factor'),
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
            'index' => Pages\ListUserTags::route('/'),
            'create' => Pages\CreateUserTag::route('/create'),
            'view' => Pages\ViewUserTag::route('/{record}'),
            'edit' => Pages\EditUserTag::route('/{record}/edit'),
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
