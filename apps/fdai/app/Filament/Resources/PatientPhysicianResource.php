<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientPhysicianResource\Pages;
use App\Filament\Resources\PatientPhysicianResource\RelationManagers;
use App\Models\PatientPhysician;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientPhysicianResource extends Resource
{
    protected static ?string $model = PatientPhysician::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('patient_user_id')
                    ->relationship('patient_user', 'ID')
                    ->required(),
                Forms\Components\Select::make('physician_user_id')
                    ->relationship('physician_user', 'ID')
                    ->required(),
                Forms\Components\TextInput::make('scopes')
                    ->required()
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
                Tables\Columns\TextColumn::make('patient_user.ID'),
                Tables\Columns\TextColumn::make('physician_user.ID'),
                Tables\Columns\TextColumn::make('scopes'),
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
            'index' => Pages\ListPatientPhysicians::route('/'),
            'create' => Pages\CreatePatientPhysician::route('/create'),
            'view' => Pages\ViewPatientPhysician::route('/{record}'),
            'edit' => Pages\EditPatientPhysician::route('/{record}/edit'),
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
