<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WpUsermetumResource\Pages;
use App\Filament\Resources\WpUsermetumResource\RelationManagers;
use App\Models\WpUsermetum;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WpUsermetumResource extends Resource
{
    protected static ?string $model = WpUsermetum::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID'),
                Forms\Components\TextInput::make('meta_key')
                    ->maxLength(255),
                Forms\Components\Textarea::make('meta_value'),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id'),
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
                Tables\Columns\TextColumn::make('meta_key'),
                Tables\Columns\TextColumn::make('meta_value'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('client.client_id'),
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
            'index' => Pages\ListWpUsermeta::route('/'),
            'create' => Pages\CreateWpUsermetum::route('/create'),
            'view' => Pages\ViewWpUsermetum::route('/{record}'),
            'edit' => Pages\EditWpUsermetum::route('/{record}/edit'),
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
