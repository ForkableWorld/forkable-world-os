<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SentEmailResource\Pages;
use App\Filament\Resources\SentEmailResource\RelationManagers;
use App\Models\SentEmail;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SentEmailResource extends Resource
{
    protected static ?string $model = SentEmail::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID'),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id'),
                Forms\Components\Select::make('wp_post_id')
                    ->relationship('wp_post', 'ID'),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('slug')
                    ->maxLength(100),
                Forms\Components\TextInput::make('response')
                    ->maxLength(140),
                Forms\Components\Textarea::make('content')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('email_address')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('subject')
                    ->required()
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
                Tables\Columns\TextColumn::make('wp_post.ID'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('response'),
                Tables\Columns\TextColumn::make('content'),
                Tables\Columns\TextColumn::make('email_address'),
                Tables\Columns\TextColumn::make('subject'),
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
            'index' => Pages\ListSentEmails::route('/'),
            'create' => Pages\CreateSentEmail::route('/create'),
            'view' => Pages\ViewSentEmail::route('/{record}'),
            'edit' => Pages\EditSentEmail::route('/{record}/edit'),
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
