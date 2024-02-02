<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\ModelTraits;
use App\Buttons\RelationshipButtons\OAClient\OAClientUserButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\DataSources\QMClient;
use App\Logging\QMLog;
use App\Models\OAClient;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use App\Types\QMStr;
trait OAClientTrait {
	public function getAppIdentifier(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_APP_IDENTIFIER] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->appIdentifier;
		}
	}
	public function setAppIdentifier(string $appIdentifier): void{
		$this->setAttribute(OAClient::FIELD_APP_IDENTIFIER, $appIdentifier);
	}
	public function getClientSecret(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_CLIENT_SECRET] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->clientSecret;
		}
	}
	public function setClientSecret(string $clientSecret): void{
		$this->setAttribute(OAClient::FIELD_CLIENT_SECRET, $clientSecret);
	}
	public function getDeletedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_DELETED_AT] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->deletedAt;
		}
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(OAClient::FIELD_DELETED_AT, $deletedAt);
	}
	public function getEarliestMeasurementStartAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_EARLIEST_MEASUREMENT_START_AT] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->earliestMeasurementStartAt;
		}
	}
	public function setEarliestMeasurementStartAt(string $earliestMeasurementStartAt): void{
		$this->setAttribute(OAClient::FIELD_EARLIEST_MEASUREMENT_START_AT, $earliestMeasurementStartAt);
	}
	public function getGrantTypes(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_GRANT_TYPES] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->grantTypes;
		}
	}
	public function setGrantTypes(string $grantTypes): void{
		$this->setAttribute(OAClient::FIELD_GRANT_TYPES, $grantTypes);
	}
	public function getIconUrl(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_ICON_URL] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->iconUrl;
		}
	}
	public function setIconUrl(string $iconUrl): void{
		$this->setAttribute(OAClient::FIELD_ICON_URL, $iconUrl);
	}
	public function getLatestMeasurementStartAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_LATEST_MEASUREMENT_START_AT] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->latestMeasurementStartAt;
		}
	}
	public function setLatestMeasurementStartAt(string $latestMeasurementStartAt): void{
		$this->setAttribute(OAClient::FIELD_LATEST_MEASUREMENT_START_AT, $latestMeasurementStartAt);
	}
	public function getNumberOfGlobalVariableRelationships(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfGlobalVariableRelationships;
		}
	}
	public function setNumberOfGlobalVariableRelationships(int $numberOfGlobalVariableRelationships): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS, $numberOfGlobalVariableRelationships);
	}
	public function getNumberOfApplications(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_APPLICATIONS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfApplications;
		}
	}
	public function setNumberOfApplications(int $numberOfApplications): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_APPLICATIONS, $numberOfApplications);
	}
	public function getNumberOfButtonClicks(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_BUTTON_CLICKS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfButtonClicks;
		}
	}
	public function setNumberOfButtonClicks(int $numberOfButtonClicks): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_BUTTON_CLICKS, $numberOfButtonClicks);
	}
	public function getNumberOfCollaborators(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_COLLABORATORS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfCollaborators;
		}
	}
	public function setNumberOfCollaborators(int $numberOfCollaborators): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_COLLABORATORS, $numberOfCollaborators);
	}
	public function getNumberOfCommonTags(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_COMMON_TAGS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfCommonTags;
		}
	}
	public function setNumberOfCommonTags(int $numberOfCommonTags): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_COMMON_TAGS, $numberOfCommonTags);
	}
	public function getNumberOfConnections(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_CONNECTIONS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfConnections;
		}
	}
	public function setNumberOfConnections(int $numberOfConnections): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_CONNECTIONS, $numberOfConnections);
	}
	public function getNumberOfConnectorImports(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_CONNECTOR_IMPORTS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfConnectorImports;
		}
	}
	public function setNumberOfConnectorImports(int $numberOfConnectorImports): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_CONNECTOR_IMPORTS, $numberOfConnectorImports);
	}
	public function getNumberOfConnectors(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_CONNECTORS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfConnectors;
		}
	}
	public function setNumberOfConnectors(int $numberOfConnectors): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_CONNECTORS, $numberOfConnectors);
	}
	public function getNumberOfCorrelations(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_CORRELATIONS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfCorrelations;
		}
	}
	public function setNumberOfCorrelations(int $numberOfCorrelations): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_CORRELATIONS, $numberOfCorrelations);
	}
	public function getNumberOfMeasurementExports(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_MEASUREMENT_EXPORTS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfMeasurementExports;
		}
	}
	public function setNumberOfMeasurementExports(int $numberOfMeasurementExports): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_MEASUREMENT_EXPORTS, $numberOfMeasurementExports);
	}
	public function getNumberOfMeasurementImports(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_MEASUREMENT_IMPORTS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfMeasurementImports;
		}
	}
	public function setNumberOfMeasurementImports(int $numberOfMeasurementImports): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_MEASUREMENT_IMPORTS, $numberOfMeasurementImports);
	}
	public function getNumberOfMeasurements(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_MEASUREMENTS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfMeasurements;
		}
	}
	public function setNumberOfMeasurements(?int $numberOfMeasurements): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_MEASUREMENTS, $numberOfMeasurements);
	}
	public function getNumberOfOauthAccessTokens(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_OAUTH_ACCESS_TOKENS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfOauthAccessTokens;
		}
	}
	public function setNumberOfOauthAccessTokens(int $numberOfOauthAccessTokens): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_OAUTH_ACCESS_TOKENS, $numberOfOauthAccessTokens);
	}
	public function getNumberOfOauthAuthorizationCodes(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_OAUTH_AUTHORIZATION_CODES] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfOauthAuthorizationCodes;
		}
	}
	public function setNumberOfOauthAuthorizationCodes(int $numberOfOauthAuthorizationCodes): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_OAUTH_AUTHORIZATION_CODES, $numberOfOauthAuthorizationCodes);
	}
	public function getNumberOfOauthRefreshTokens(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_OAUTH_REFRESH_TOKENS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfOauthRefreshTokens;
		}
	}
	public function setNumberOfOauthRefreshTokens(int $numberOfOauthRefreshTokens): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_OAUTH_REFRESH_TOKENS, $numberOfOauthRefreshTokens);
	}
	public function getNumberOfSentEmails(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_SENT_EMAILS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfSentEmails;
		}
	}
	public function setNumberOfSentEmails(int $numberOfSentEmails): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_SENT_EMAILS, $numberOfSentEmails);
	}
	public function getNumberOfStudies(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_STUDIES] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfStudies;
		}
	}
	public function setNumberOfStudies(int $numberOfStudies): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_STUDIES, $numberOfStudies);
	}
	public function getNumberOfTrackingReminderNotifications(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfTrackingReminderNotifications;
		}
	}

	public function getNumberOfTrackingReminders(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_TRACKING_REMINDERS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfTrackingReminders;
		}
	}
	public function setNumberOfTrackingReminders(int $numberOfTrackingReminders): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_TRACKING_REMINDERS, $numberOfTrackingReminders);
	}
	public function getNumberOfUserTags(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_USER_TAGS] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfUserTags;
		}
	}
	public function setNumberOfUserTags(int $numberOfUserTags): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_USER_TAGS, $numberOfUserTags);
	}
	public function getNumberOfUserVariables(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_USER_VARIABLES] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfUserVariables;
		}
	}
	public function setNumberOfUserVariables(int $numberOfUserVariables): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_USER_VARIABLES, $numberOfUserVariables);
	}
	public function getNumberOfVariables(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_VARIABLES] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfVariables;
		}
	}
	public function setNumberOfVariables(int $numberOfVariables): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_VARIABLES, $numberOfVariables);
	}
	public function getNumberOfVotes(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[OAClient::FIELD_NUMBER_OF_VOTES] ?? null;
		} else{
			/** @var QMClient $this */
			return $this->numberOfVotes;
		}
	}
	public function setNumberOfVotes(int $numberOfVotes): void{
		$this->setAttribute(OAClient::FIELD_NUMBER_OF_VOTES, $numberOfVotes);
	}
	public function getRedirectUri(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			$uris = $this->attributes[OAClient::FIELD_REDIRECT_URI] ?? null;
		} else{
			/** @var QMClient $this */
			$uris = $this->redirectUri;
		}
		if(!$uris){
			return null;
		}
		$ploded = explode(" ", $uris);
		$uri = $ploded[0] ?? null;
		if(!$uri){
			return null;
		}
		if(str_contains($uri, 'ionic/Modo/www/callback')){
			QMLog::error("Not returning ionic deprecated redirect uri: $uri");
			return null;
		}
		return $uri;
	}
	public function setRedirectUri(string $redirectUri): void{
		$this->setAttribute(OAClient::FIELD_REDIRECT_URI, $redirectUri);
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new OAClientUserButton($this),
		];
	}
	/**
	 * @return static
	 */
	public static function system(): self{
		return static::first(BaseClientIdProperty::CLIENT_ID_SYSTEM);
	}
	protected static function findOrCreateSystemClient(string $clientId){
		return static::findInMemoryDBOrCreate([
			OAClient::FIELD_USER_ID => UserIdProperty::USER_ID_SYSTEM,
			OAClient::FIELD_CLIENT_ID => $clientId,
			OAClient::FIELD_CLIENT_SECRET => QMStr::random(10),
		]);
	}
	public static function moneymodo(): self{
		return static::findOrCreateSystemClient(BaseClientIdProperty::CLIENT_ID_MONEYMODO);
	}
	public static function clinicalTrialsGov(){
		return static::findOrCreateSystemClient(BaseClientIdProperty::CLIENT_ID_CLINICAL_TRIALS_GOV);
	}
}
