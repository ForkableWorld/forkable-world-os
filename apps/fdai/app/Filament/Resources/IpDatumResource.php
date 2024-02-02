<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IpDatumResource\Pages;
use App\Filament\Resources\IpDatumResource\RelationManagers;
use App\Models\IpDatum;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IpDatumResource extends Resource
{
    protected static ?string $model = IpDatum::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required(),
                Forms\Components\TextInput::make('ip')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('hostname')
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('continent_code')
                    ->maxLength(255),
                Forms\Components\TextInput::make('continent_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('country_code')
                    ->maxLength(255),
                Forms\Components\TextInput::make('country_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('region_code')
                    ->maxLength(255),
                Forms\Components\TextInput::make('region_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('city')
                    ->maxLength(255),
                Forms\Components\TextInput::make('zip')
                    ->maxLength(255),
                Forms\Components\TextInput::make('latitude'),
                Forms\Components\TextInput::make('longitude'),
                Forms\Components\Textarea::make('location'),
                Forms\Components\Textarea::make('time_zone'),
                Forms\Components\Textarea::make('currency'),
                Forms\Components\Textarea::make('connection'),
                Forms\Components\Textarea::make('security'),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('ip'),
                Tables\Columns\TextColumn::make('hostname'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('continent_code'),
                Tables\Columns\TextColumn::make('continent_name'),
                Tables\Columns\TextColumn::make('country_code'),
                Tables\Columns\TextColumn::make('country_name'),
                Tables\Columns\TextColumn::make('region_code'),
                Tables\Columns\TextColumn::make('region_name'),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('zip'),
                Tables\Columns\TextColumn::make('latitude'),
                Tables\Columns\TextColumn::make('longitude'),
                Tables\Columns\TextColumn::make('location'),
                Tables\Columns\TextColumn::make('time_zone'),
                Tables\Columns\TextColumn::make('currency'),
                Tables\Columns\TextColumn::make('connection'),
                Tables\Columns\TextColumn::make('security'),
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
            'index' => Pages\ListIpData::route('/'),
            'view' => Pages\ViewIpDatum::route('/{record}'),
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
