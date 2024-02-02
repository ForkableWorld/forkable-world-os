<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudyResource\Pages;
use App\Filament\Resources\StudyResource\RelationManagers;
use App\Models\Study;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudyResource extends Resource
{
    protected static ?string $model = Study::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(80),
                Forms\Components\Select::make('cause_variable_id')
                    ->relationship('cause_variable', 'name')
                    ->required(),
                Forms\Components\Select::make('effect_variable_id')
                    ->relationship('effect_variable', 'name')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'ID')
                    ->required(),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'client_id'),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(20),
                Forms\Components\Textarea::make('analysis_parameters')
                    ->maxLength(65535),
                Forms\Components\Textarea::make('user_study_text'),
                Forms\Components\Textarea::make('user_title')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('study_status')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('comment_status')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('study_password')
                    ->password()
                    ->maxLength(20),
                Forms\Components\Textarea::make('study_images')
                    ->maxLength(65535),
                Forms\Components\DateTimePicker::make('published_at'),
                Forms\Components\TextInput::make('wp_post_id'),
                Forms\Components\DateTimePicker::make('newest_data_at'),
                Forms\Components\DateTimePicker::make('analysis_requested_at'),
                Forms\Components\TextInput::make('reason_for_analysis')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('analysis_ended_at'),
                Forms\Components\DateTimePicker::make('analysis_started_at'),
                Forms\Components\TextInput::make('internal_error_message')
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_error_message')
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->maxLength(25),
                Forms\Components\DateTimePicker::make('analysis_settings_modified_at'),
                Forms\Components\Toggle::make('is_public'),
                Forms\Components\TextInput::make('sort_order')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->maxLength(200),
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
                Tables\Columns\TextColumn::make('cause_variable.name'),
                Tables\Columns\TextColumn::make('effect_variable.name'),
                Tables\Columns\TextColumn::make('user.ID'),
                Tables\Columns\TextColumn::make('client.client_id'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('analysis_parameters'),
                Tables\Columns\TextColumn::make('user_study_text'),
                Tables\Columns\TextColumn::make('user_title'),
                Tables\Columns\TextColumn::make('study_status'),
                Tables\Columns\TextColumn::make('comment_status'),
                Tables\Columns\TextColumn::make('study_images'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('wp_post_id'),
                Tables\Columns\TextColumn::make('newest_data_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('analysis_requested_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('reason_for_analysis'),
                Tables\Columns\TextColumn::make('analysis_ended_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('analysis_started_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('internal_error_message'),
                Tables\Columns\TextColumn::make('user_error_message'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('analysis_settings_modified_at')
                    ->dateTime(),
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order'),
                Tables\Columns\TextColumn::make('slug'),
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
            'index' => Pages\ListStudies::route('/'),
            'create' => Pages\CreateStudy::route('/create'),
            'view' => Pages\ViewStudy::route('/{record}'),
            'edit' => Pages\EditStudy::route('/{record}/edit'),
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
