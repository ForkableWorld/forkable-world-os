<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrackingReminderResource\Pages;
use App\Filament\Resources\TrackingReminderResource\RelationManagers;
use App\Models\TrackingReminder;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TrackingReminderResource extends Resource
{
    protected static ?string $model = TrackingReminder::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID')
                    ->required(),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id')
                    ->required(),
                Forms\Components\Select::make('variable_id')
                    ->relationship('variable', 'name')
                    ->required(),
                Forms\Components\Select::make('user_variable_id')
                    ->relationship('user_variable', 'id')
                    ->required(),
                Forms\Components\TextInput::make('default_value'),
                Forms\Components\TextInput::make('reminder_start_time')
                    ->required(),
                Forms\Components\TextInput::make('reminder_end_time'),
                Forms\Components\TextInput::make('reminder_sound')
                    ->maxLength(125),
                Forms\Components\TextInput::make('reminder_frequency'),
                Forms\Components\Toggle::make('pop_up'),
                Forms\Components\Toggle::make('sms'),
                Forms\Components\Toggle::make('email'),
                Forms\Components\Toggle::make('notification_bar'),
                Forms\Components\DateTimePicker::make('last_tracked'),
                Forms\Components\DatePicker::make('start_tracking_date'),
                Forms\Components\DatePicker::make('stop_tracking_date'),
                Forms\Components\Textarea::make('instructions')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('image_url')
                    ->maxLength(2083),
                Forms\Components\DateTimePicker::make('latest_tracking_reminder_notification_notify_at'),
                Forms\Components\TextInput::make('number_of_tracking_reminder_notifications'),
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
                Tables\Columns\TextColumn::make('variable.name'),
                Tables\Columns\TextColumn::make('user_variable.id'),
                Tables\Columns\TextColumn::make('default_value'),
                Tables\Columns\TextColumn::make('reminder_start_time'),
                Tables\Columns\TextColumn::make('reminder_end_time'),
                Tables\Columns\TextColumn::make('reminder_sound'),
                Tables\Columns\TextColumn::make('reminder_frequency'),
                Tables\Columns\IconColumn::make('pop_up')
                    ->boolean(),
                Tables\Columns\IconColumn::make('sms')
                    ->boolean(),
                Tables\Columns\IconColumn::make('email')
                    ->boolean(),
                Tables\Columns\IconColumn::make('notification_bar')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_tracked')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('start_tracking_date')
                    ->date(),
                Tables\Columns\TextColumn::make('stop_tracking_date')
                    ->date(),
                Tables\Columns\TextColumn::make('instructions'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('image_url'),
                Tables\Columns\TextColumn::make('latest_tracking_reminder_notification_notify_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('number_of_tracking_reminder_notifications'),
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
            'index' => Pages\ListTrackingReminders::route('/'),
            'create' => Pages\CreateTrackingReminder::route('/create'),
            'view' => Pages\ViewTrackingReminder::route('/{record}'),
            'edit' => Pages\EditTrackingReminder::route('/{record}/edit'),
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
