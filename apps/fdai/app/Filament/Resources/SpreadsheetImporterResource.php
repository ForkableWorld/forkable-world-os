<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpreadsheetImporterResource\Pages;
use App\Filament\Resources\SpreadsheetImporterResource\RelationManagers;
use App\Models\SpreadsheetImporter;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpreadsheetImporterResource extends Resource
{
    protected static ?string $model = SpreadsheetImporter::class;

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
                Forms\Components\TextInput::make('number_of_measurement_imports'),
                Forms\Components\TextInput::make('number_of_measurements'),
                Forms\Components\TextInput::make('sort_order')
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('number_of_measurement_imports'),
                Tables\Columns\TextColumn::make('number_of_measurements'),
                Tables\Columns\TextColumn::make('sort_order'),
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
            'index' => Pages\ListSpreadsheetImporters::route('/'),
            'create' => Pages\CreateSpreadsheetImporter::route('/create'),
            'view' => Pages\ViewSpreadsheetImporter::route('/{record}'),
            'edit' => Pages\EditSpreadsheetImporter::route('/{record}/edit'),
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
