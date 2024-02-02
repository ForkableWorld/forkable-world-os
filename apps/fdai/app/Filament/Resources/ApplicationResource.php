<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use Exception;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

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
                Forms\Components\Select::make('outcome_variable_id')
                    ->relationship('outcome_variable', 'name'),
                Forms\Components\Select::make('predictor_variable_id')
                    ->relationship('predictor_variable', 'name'),
                Forms\Components\Select::make('wp_post_id')
                    ->relationship('wp_post', 'ID'),
                Forms\Components\TextInput::make('organization_id'),
                Forms\Components\TextInput::make('app_display_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('app_description')
                    ->maxLength(255),
                Forms\Components\Textarea::make('long_description')
                    ->maxLength(65535),
                Forms\Components\TextInput::make('icon_url')
                    ->maxLength(2083),
                Forms\Components\TextInput::make('text_logo')
                    ->maxLength(2083),
                Forms\Components\TextInput::make('splash_screen')
                    ->maxLength(2083),
                Forms\Components\TextInput::make('homepage_url')
                    ->maxLength(255),
                Forms\Components\TextInput::make('app_type')
                    ->maxLength(32),
                Forms\Components\Textarea::make('app_design')
                    ->maxLength(16777215),
                Forms\Components\Toggle::make('enabled')
                    ->required(),
                Forms\Components\Toggle::make('stripe_active')
                    ->required(),
                Forms\Components\TextInput::make('stripe_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('stripe_subscription')
                    ->maxLength(255),
                Forms\Components\TextInput::make('stripe_plan')
                    ->maxLength(100),
                Forms\Components\TextInput::make('last_four')
                    ->maxLength(4),
                Forms\Components\DateTimePicker::make('trial_ends_at'),
                Forms\Components\DateTimePicker::make('subscription_ends_at'),
                Forms\Components\TextInput::make('company_name')
                    ->maxLength(100),
                Forms\Components\TextInput::make('country')
                    ->maxLength(100),
                Forms\Components\TextInput::make('address')
                    ->maxLength(255),
                Forms\Components\TextInput::make('state')
                    ->maxLength(100),
                Forms\Components\TextInput::make('city')
                    ->maxLength(100),
                Forms\Components\TextInput::make('zip')
                    ->maxLength(10),
                Forms\Components\TextInput::make('plan_id'),
                Forms\Components\TextInput::make('exceeding_call_count')
                    ->required(),
                Forms\Components\TextInput::make('exceeding_call_charge'),
                Forms\Components\Toggle::make('study')
                    ->required(),
                Forms\Components\Toggle::make('billing_enabled')
                    ->required(),
                Forms\Components\Toggle::make('physician')
                    ->required(),
                Forms\Components\Textarea::make('additional_settings')
                    ->maxLength(65535),
                Forms\Components\Textarea::make('app_status')
                    ->maxLength(65535),
                Forms\Components\Toggle::make('build_enabled')
                    ->required(),
                Forms\Components\TextInput::make('number_of_collaborators_where_app'),
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
                Tables\Columns\TextColumn::make('client.client_id'),
//                Tables\Columns\TextColumn::make('user.ID'),
//                Tables\Columns\TextColumn::make('outcome_variable.name'),
//                Tables\Columns\TextColumn::make('predictor_variable.name'),
//                Tables\Columns\TextColumn::make('wp_post.ID'),
//                Tables\Columns\TextColumn::make('organization_id'),
                Tables\Columns\TextColumn::make('app_display_name'),
                Tables\Columns\TextColumn::make('app_description'),
//                Tables\Columns\TextColumn::make('long_description'),
                Tables\Columns\TextColumn::make('icon_url'),
//                Tables\Columns\TextColumn::make('text_logo'),
//                Tables\Columns\TextColumn::make('splash_screen'),
//                Tables\Columns\TextColumn::make('homepage_url'),
                Tables\Columns\TextColumn::make('app_type'),
//                Tables\Columns\TextColumn::make('app_design'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
//                Tables\Columns\TextColumn::make('deleted_at')
//                    ->dateTime(),
//                Tables\Columns\IconColumn::make('enabled')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('stripe_active')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('stripe_id'),
//                Tables\Columns\TextColumn::make('stripe_subscription'),
//                Tables\Columns\TextColumn::make('stripe_plan'),
//                Tables\Columns\TextColumn::make('last_four'),
//                Tables\Columns\TextColumn::make('trial_ends_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('subscription_ends_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('company_name'),
//                Tables\Columns\TextColumn::make('country'),
//                Tables\Columns\TextColumn::make('address'),
//                Tables\Columns\TextColumn::make('state'),
//                Tables\Columns\TextColumn::make('city'),
//                Tables\Columns\TextColumn::make('zip'),
//                Tables\Columns\TextColumn::make('plan_id'),
//                Tables\Columns\TextColumn::make('exceeding_call_count'),
//                Tables\Columns\TextColumn::make('exceeding_call_charge'),
//                Tables\Columns\IconColumn::make('study')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('billing_enabled')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('physician')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('additional_settings'),
//                Tables\Columns\TextColumn::make('app_status'),
//                Tables\Columns\IconColumn::make('build_enabled')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('number_of_collaborators_where_app'),
//                Tables\Columns\IconColumn::make('is_public')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('sort_order'),
//                Tables\Columns\TextColumn::make('slug'),
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
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'view' => Pages\ViewApplication::route('/{record}'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
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
