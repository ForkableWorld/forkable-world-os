<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommonTagResource\Pages;
use App\Filament\Resources\CommonTagResource\RelationManagers;
use App\Models\CommonTag;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommonTagResource extends Resource
{
    protected static ?string $model = CommonTag::class;

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
                Forms\Components\Select::make('tag_variable_unit_id')
                    ->relationship('tag_variable_unit', 'name'),
                Forms\Components\Select::make('tagged_variable_unit_id')
                    ->relationship('tagged_variable_unit', 'name'),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id'),
                Forms\Components\TextInput::make('number_of_data_points'),
                Forms\Components\TextInput::make('standard_error'),
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
                Tables\Columns\TextColumn::make('tag_variable_unit.name'),
                Tables\Columns\TextColumn::make('tagged_variable_unit.name'),
                Tables\Columns\TextColumn::make('client.client_id'),
                Tables\Columns\TextColumn::make('number_of_data_points'),
                Tables\Columns\TextColumn::make('standard_error'),
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
            'index' => Pages\ListCommonTags::route('/'),
            'create' => Pages\CreateCommonTag::route('/create'),
            'view' => Pages\ViewCommonTag::route('/{record}'),
            'edit' => Pages\EditCommonTag::route('/{record}/edit'),
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
