<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeasurementImportResource\Pages;
use App\Filament\Resources\MeasurementImportResource\RelationManagers;
use App\Models\MeasurementImport;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MeasurementImportResource extends Resource
{
    protected static ?string $model = MeasurementImport::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID')
                    ->required(),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id'),
                Forms\Components\TextInput::make('file')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(25),
                Forms\Components\Textarea::make('error_message')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('source_name')
                    ->maxLength(80),
                Forms\Components\DateTimePicker::make('import_started_at'),
                Forms\Components\DateTimePicker::make('import_ended_at'),
                Forms\Components\TextInput::make('reason_for_import')
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_error_message')
                    ->maxLength(255),
                Forms\Components\TextInput::make('internal_error_message')
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('client.client_id'),
                Tables\Columns\TextColumn::make('file'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('error_message'),
                Tables\Columns\TextColumn::make('source_name'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('import_started_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('import_ended_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('reason_for_import'),
                Tables\Columns\TextColumn::make('user_error_message'),
                Tables\Columns\TextColumn::make('internal_error_message'),
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
            'index' => Pages\ListMeasurementImports::route('/'),
            'create' => Pages\CreateMeasurementImport::route('/create'),
            'view' => Pages\ViewMeasurementImport::route('/{record}'),
            'edit' => Pages\EditMeasurementImport::route('/{record}/edit'),
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
