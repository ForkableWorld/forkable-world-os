<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Exception;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
//                Forms\Components\Select::make('referrer_user_id')
//                    ->relationship('referrer_user', 'ID'),
//                Forms\Components\Select::make('primary_outcome_variable_id')
//                    ->relationship('primary_outcome_variable', 'name'),
//                Forms\Components\Select::make('wp_post_id')
//                    ->relationship('wp_post', 'ID'),
//                Forms\Components\Select::make('client_id')
//                    ->relationship('client', 'client_id')
//                    ->required(),
//                TextInput::make('user_login')
//                    ->maxLength(60),
//                TextInput::make('user_email')
//                    ->email()
//                    ->maxLength(100),
                TextInput::make('email')
                    ->email()
                    ->maxLength(320),
//                TextInput::make('user_pass')->maxLength(255),
//                TextInput::make('user_nicename')->maxLength(50),
//                TextInput::make('user_url')->maxLength(2083),
//                Forms\Components\DateTimePicker::make('user_registered'),
//                TextInput::make('user_activation_key')->maxLength(255),
//                TextInput::make('user_status'),
                TextInput::make('display_name')->maxLength(250),
                FileUpload::make('avatar_image')->avatar(),
//                TextInput::make('reg_provider')
//                    ->maxLength(25),
//                TextInput::make('provider_id')
//                    ->maxLength(255),
//                Forms\Components\Toggle::make('unsubscribed'),
//                Forms\Components\Toggle::make('old_user'),
//                Forms\Components\Toggle::make('stripe_active'),
//                TextInput::make('stripe_id')
//                    ->maxLength(255),
//                TextInput::make('stripe_subscription')
//                    ->maxLength(255),
//                TextInput::make('stripe_plan')
//                    ->maxLength(100),
//                TextInput::make('last_four')
//                    ->maxLength(4),
//                Forms\Components\DateTimePicker::make('trial_ends_at'),
//                Forms\Components\DateTimePicker::make('subscription_ends_at'),
//                TextInput::make('roles')
//                    ->maxLength(255),
//                TextInput::make('time_zone_offset'),
//                TextInput::make('earliest_reminder_time')
//                    ->required(),
//                TextInput::make('latest_reminder_time')
//                    ->required(),
//                Forms\Components\Toggle::make('push_notifications_enabled'),
//                Forms\Components\Toggle::make('track_location'),
//                Forms\Components\Toggle::make('combine_notifications'),
//                Forms\Components\Toggle::make('send_reminder_notification_emails'),
//                Forms\Components\Toggle::make('send_predictor_emails'),
//                Forms\Components\Toggle::make('get_preview_builds'),
//                TextInput::make('subscription_provider'),
//                TextInput::make('last_sms_tracking_reminder_notification_id'),
//                Forms\Components\Toggle::make('sms_notifications_enabled'),
//                TextInput::make('phone_verification_code')
//                    ->tel()
//                    ->maxLength(25),
//                TextInput::make('phone_number')
//                    ->tel()
//                    ->maxLength(25),
//                Forms\Components\Toggle::make('has_android_app'),
//                Forms\Components\Toggle::make('has_ios_app'),
//                Forms\Components\Toggle::make('has_chrome_extension'),
//                TextInput::make('address')
//                    ->maxLength(255),
//                TextInput::make('birthday')
//                    ->maxLength(255),
//                TextInput::make('country')
//                    ->maxLength(255),
//                TextInput::make('cover_photo')
//                    ->maxLength(2083),
//                TextInput::make('currency')
//                    ->maxLength(255),
//                TextInput::make('first_name')
//                    ->maxLength(255),
//                TextInput::make('gender')
//                    ->maxLength(255),
//                TextInput::make('language')
//                    ->maxLength(255),
//                TextInput::make('last_name')
//                    ->maxLength(255),
//                TextInput::make('state')
//                    ->maxLength(255),
//                TextInput::make('tag_line')
//                    ->maxLength(255),
//                TextInput::make('verified')
//                    ->maxLength(255),
//                TextInput::make('zip_code')
//                    ->maxLength(255),
//                Forms\Components\Toggle::make('spam')
//                    ->required(),
//                Forms\Components\Toggle::make('deleted')
//                    ->required(),
//                TextInput::make('card_brand')
//                    ->maxLength(255),
//                TextInput::make('card_last_four')
//                    ->maxLength(4),
//                Forms\Components\DateTimePicker::make('last_login_at'),
//                TextInput::make('timezone')
//                    ->maxLength(255),
//                TextInput::make('number_of_correlations'),
//                TextInput::make('number_of_connections'),
//                TextInput::make('number_of_tracking_reminders'),
//                TextInput::make('number_of_user_variables'),
//                TextInput::make('number_of_raw_measurements_with_tags'),
//                TextInput::make('number_of_raw_measurements_with_tags_at_last_correlation'),
//                TextInput::make('number_of_votes'),
//                TextInput::make('number_of_studies'),
//                Forms\Components\DateTimePicker::make('last_correlation_at'),
//                Forms\Components\DateTimePicker::make('last_email_at'),
//                Forms\Components\DateTimePicker::make('last_push_at'),
//                Forms\Components\DateTimePicker::make('analysis_ended_at'),
//                Forms\Components\DateTimePicker::make('analysis_requested_at'),
//                Forms\Components\DateTimePicker::make('analysis_started_at'),
//                Forms\Components\Textarea::make('internal_error_message')
//                    ->maxLength(65535),
//                Forms\Components\DateTimePicker::make('newest_data_at'),
//                TextInput::make('reason_for_analysis')
//                    ->maxLength(255),
//                Forms\Components\Textarea::make('user_error_message')
//                    ->maxLength(65535),
//                TextInput::make('status')
//                    ->maxLength(25),
//                Forms\Components\DateTimePicker::make('analysis_settings_modified_at'),
//                TextInput::make('number_of_applications'),
//                TextInput::make('number_of_oauth_access_tokens'),
//                TextInput::make('number_of_oauth_authorization_codes'),
//                TextInput::make('number_of_oauth_clients'),
//                TextInput::make('number_of_oauth_refresh_tokens'),
//                TextInput::make('number_of_button_clicks'),
//                TextInput::make('number_of_collaborators'),
//                TextInput::make('number_of_connector_imports'),
//                TextInput::make('number_of_connector_requests'),
//                TextInput::make('number_of_measurement_exports'),
//                TextInput::make('number_of_measurement_imports'),
//                TextInput::make('number_of_measurements'),
//                TextInput::make('number_of_sent_emails')
//                    ->email(),
//                TextInput::make('number_of_subscriptions'),
//                TextInput::make('number_of_tracking_reminder_notifications'),
//                TextInput::make('number_of_user_tags'),
//                TextInput::make('number_of_users_where_referrer_user'),
//                Forms\Components\Toggle::make('share_all_data')
//                    ->required(),
//                TextInput::make('deletion_reason')
//                    ->maxLength(280),
//                TextInput::make('password')
//                    ->password()
//                    ->maxLength(255),
//                TextInput::make('number_of_patients')
//                    ->required(),
//                Forms\Components\Toggle::make('is_public'),
//                TextInput::make('sort_order')
//                    ->required(),
//                TextInput::make('number_of_sharers')
//                    ->required(),
//                TextInput::make('number_of_trustees')
//                    ->required(),
//                TextInput::make('slug')
//                    ->maxLength(200),
//                TextInput::make('eth_address')
//                    ->maxLength(255),
//                TextInput::make('salt')
//                    ->maxLength(255),
            ]);
    }
	/**
	 * @throws Exception
	 */
	public static function table(Table $table): Table
    {
        return $table
	        ->defaultSort(User::FIELD_LAST_LOGIN_AT, 'desc')
            ->columns([
	                      ImageColumn::make('avatar_image')
	                                 ->label(null),
	                      TextColumn::make('display_name')
	                                               ->label('Name')
	                                               ->searchable(),
//                TextColumn::make('referrer_user.ID'),
//                TextColumn::make('primary_outcome_variable.name'),
//                TextColumn::make('wp_post.ID'),
//                TextColumn::make('client.client_id'),
                TextColumn::make('user_login'),
//                TextColumn::make('user_email'),
                TextColumn::make('email'),
//                TextColumn::make('user_pass'),
//                TextColumn::make('user_nicename'),
//                TextColumn::make('user_url'),
//                TextColumn::make('user_registered')
//                    ->dateTime(),
//                TextColumn::make('user_activation_key'),
//                TextColumn::make('user_status'),
//                TextColumn::make('reg_provider'),
//                TextColumn::make('provider_id'),
//                TextColumn::make('updated_at')
//                    ->dateTime(),
//                TextColumn::make('created_at')
//                    ->dateTime(),
//                Tables\Columns\IconColumn::make('unsubscribed')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('old_user')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('stripe_active')
//                    ->boolean(),
//                TextColumn::make('stripe_id'),
//                TextColumn::make('stripe_subscription'),
//                TextColumn::make('stripe_plan'),
//                TextColumn::make('last_four'),
//                TextColumn::make('trial_ends_at')
//                    ->dateTime(),
//                TextColumn::make('subscription_ends_at')
//                    ->dateTime(),
//                TextColumn::make('roles'),
//                TextColumn::make('time_zone_offset'),
//                TextColumn::make('deleted_at')
//                    ->dateTime(),
//                TextColumn::make('earliest_reminder_time'),
//                TextColumn::make('latest_reminder_time'),
//                Tables\Columns\IconColumn::make('push_notifications_enabled')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('track_location')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('combine_notifications')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('send_reminder_notification_emails')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('send_predictor_emails')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('get_preview_builds')
//                    ->boolean(),
//                TextColumn::make('subscription_provider'),
//                TextColumn::make('last_sms_tracking_reminder_notification_id'),
//                Tables\Columns\IconColumn::make('sms_notifications_enabled')
//                    ->boolean(),
//                TextColumn::make('phone_verification_code'),
//                TextColumn::make('phone_number'),
//                Tables\Columns\IconColumn::make('has_android_app')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('has_ios_app')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('has_chrome_extension')
//                    ->boolean(),
//                TextColumn::make('address'),
//                TextColumn::make('birthday'),
//                TextColumn::make('country'),
//                TextColumn::make('cover_photo'),
//                TextColumn::make('currency'),
//                TextColumn::make('first_name'),
//                TextColumn::make('gender'),
//                TextColumn::make('language'),
//                TextColumn::make('last_name'),
//                TextColumn::make('state'),
//                TextColumn::make('tag_line'),
//                TextColumn::make('verified'),
//                TextColumn::make('zip_code'),
//                Tables\Columns\IconColumn::make('spam')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('deleted')
//                    ->boolean(),
//                TextColumn::make('card_brand'),
//                TextColumn::make('card_last_four'),
//                TextColumn::make('last_login_at')
//                    ->dateTime(),
//                TextColumn::make('timezone'),
//                TextColumn::make('number_of_correlations'),
//                TextColumn::make('number_of_connections'),
//                TextColumn::make('number_of_tracking_reminders'),
//                TextColumn::make('number_of_user_variables'),
//                TextColumn::make('number_of_raw_measurements_with_tags'),
//                TextColumn::make('number_of_raw_measurements_with_tags_at_last_correlation'),
//                TextColumn::make('number_of_votes'),
//                TextColumn::make('number_of_studies'),
//                TextColumn::make('last_correlation_at')
//                    ->dateTime(),
//                TextColumn::make('last_email_at')
//                    ->dateTime(),
//                TextColumn::make('last_push_at')
//                    ->dateTime(),
//                TextColumn::make('analysis_ended_at')
//                    ->dateTime(),
//                TextColumn::make('analysis_requested_at')
//                    ->dateTime(),
//                TextColumn::make('analysis_started_at')
//                    ->dateTime(),
//                TextColumn::make('internal_error_message'),
//                TextColumn::make('newest_data_at')
//                    ->dateTime(),
//                TextColumn::make('reason_for_analysis'),
//                TextColumn::make('user_error_message'),
//                TextColumn::make('status'),
//                TextColumn::make('analysis_settings_modified_at')
//                    ->dateTime(),
//                TextColumn::make('number_of_applications'),
//                TextColumn::make('number_of_oauth_access_tokens'),
//                TextColumn::make('number_of_oauth_authorization_codes'),
//                TextColumn::make('number_of_oauth_clients'),
//                TextColumn::make('number_of_oauth_refresh_tokens'),
//                TextColumn::make('number_of_button_clicks'),
//                TextColumn::make('number_of_collaborators'),
//                TextColumn::make('number_of_connector_imports'),
//                TextColumn::make('number_of_connector_requests'),
//                TextColumn::make('number_of_measurement_exports'),
//                TextColumn::make('number_of_measurement_imports'),
                TextColumn::make('number_of_measurements'),
//                TextColumn::make('number_of_sent_emails'),
//                TextColumn::make('number_of_subscriptions'),
//                TextColumn::make('number_of_tracking_reminder_notifications'),
//                TextColumn::make('number_of_user_tags'),
//                TextColumn::make('number_of_users_where_referrer_user'),
//                Tables\Columns\IconColumn::make('share_all_data')
//                    ->boolean(),
//                TextColumn::make('deletion_reason'),
//                TextColumn::make('number_of_patients'),
//                Tables\Columns\IconColumn::make('is_public')
//                    ->boolean(),
//                TextColumn::make('sort_order'),
//                TextColumn::make('number_of_sharers'),
//                TextColumn::make('number_of_trustees'),
//                TextColumn::make('slug'),
//                TextColumn::make('eth_address'),
//                TextColumn::make('salt'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
//            ->bulkActions([
//                Tables\Actions\DeleteBulkAction::make(),
//            ])
	        ;
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }    
}
