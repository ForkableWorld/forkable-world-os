<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NftResource\Pages;
use App\Filament\Resources\NftResource\RelationManagers;
use App\Models\Nft;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NftResource extends Resource
{
    protected static ?string $model = Nft::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required(),
                Forms\Components\TextInput::make('tokenizable_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tokenizable_id')
                    ->required(),
                Forms\Components\TextInput::make('chain')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('token_address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('token_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('social_media_url')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('quantity')
                    ->required(),
                Forms\Components\TextInput::make('minting_address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('file_url')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ipfs_cid')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tx_hash')
                    ->required()
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('user_id'),
                Tables\Columns\TextColumn::make('tokenizable_type'),
                Tables\Columns\TextColumn::make('tokenizable_id'),
                Tables\Columns\TextColumn::make('chain'),
                Tables\Columns\TextColumn::make('token_address'),
                Tables\Columns\TextColumn::make('token_id'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('social_media_url'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('minting_address'),
                Tables\Columns\TextColumn::make('file_url'),
                Tables\Columns\TextColumn::make('ipfs_cid'),
                Tables\Columns\TextColumn::make('tx_hash'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListNfts::route('/'),
            'create' => Pages\CreateNft::route('/create'),
            'view' => Pages\ViewNft::route('/{record}'),
            'edit' => Pages\EditNft::route('/{record}/edit'),
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
