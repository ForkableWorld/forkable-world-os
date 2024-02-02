<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhraseResource\Pages;
use App\Filament\Resources\PhraseResource\RelationManagers;
use App\Models\Phrase;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PhraseResource extends Resource
{
    protected static ?string $model = Phrase::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID')
                    ->required(),
                Forms\Components\TextInput::make('image')
                    ->maxLength(100),
                Forms\Components\Textarea::make('text')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\TextInput::make('title')
                    ->maxLength(80),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(80),
                Forms\Components\TextInput::make('url')
                    ->maxLength(100),
                Forms\Components\TextInput::make('responding_to_phrase_id'),
                Forms\Components\TextInput::make('response_phrase_id'),
                Forms\Components\Textarea::make('recipient_user_ids')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('number_of_times_heard'),
                Forms\Components\TextInput::make('interpretative_confidence'),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('image'),
                Tables\Columns\TextColumn::make('text'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('url'),
                Tables\Columns\TextColumn::make('responding_to_phrase_id'),
                Tables\Columns\TextColumn::make('response_phrase_id'),
                Tables\Columns\TextColumn::make('recipient_user_ids'),
                Tables\Columns\TextColumn::make('number_of_times_heard'),
                Tables\Columns\TextColumn::make('interpretative_confidence'),
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
            'index' => Pages\ListPhrases::route('/'),
            'create' => Pages\CreatePhrase::route('/create'),
            'view' => Pages\ViewPhrase::route('/{record}'),
            'edit' => Pages\EditPhrase::route('/{record}/edit'),
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
