<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceTokenResource\Pages;
use App\Filament\Resources\DeviceTokenResource\RelationManagers;
use App\Models\DeviceToken;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeviceTokenResource extends Resource
{
    protected static ?string $model = DeviceToken::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
	/**
	 * @throws Exception
	 */
	public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.ID'),
                Tables\Columns\TextColumn::make('client.client_id'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('number_of_waiting_tracking_reminder_notifications'),
                Tables\Columns\TextColumn::make('last_notified_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('platform'),
                Tables\Columns\TextColumn::make('number_of_new_tracking_reminder_notifications'),
                Tables\Columns\TextColumn::make('number_of_notifications_last_sent'),
                Tables\Columns\TextColumn::make('error_message'),
                Tables\Columns\TextColumn::make('last_checked_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('received_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('server_ip'),
                Tables\Columns\TextColumn::make('server_hostname'),
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
            'index' => Pages\ListDeviceTokens::route('/'),
            'view' => Pages\ViewDeviceToken::route('/{record}'),
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
