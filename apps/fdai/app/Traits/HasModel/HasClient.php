<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\GlobalVariableRelationship;
use App\Models\OAClient;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
trait HasClient {
	public function getClientId(): ?string{
		$nameOrId = $this->getAttribute('client_id');
		return $nameOrId;
	}
	public function getClientButton(): ?QMButton{
		$nameOrId = $this->getClientId();
		if(!$nameOrId){
			return null;
		}
		return OAClient::generateDataLabShowButton($nameOrId);
	}
	public function getClientLink(): string{
		$b = $this->getClientButton();
		if(!$b){
			return "N/A";
		}
		return $b->getNameLink();
	}
	/** @noinspection PhpUnused */
	public function getOAClientLink(): string{
		return $this->getClientLink();
	}
	public function client(): BelongsTo{ return $this->oa_client(); }
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, GlobalVariableRelationship::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			GlobalVariableRelationship::FIELD_CLIENT_ID);
	}
	public function getClient():OAClient{
		$clientId = $this->getClientId();
		$client = OAClient::findInMemoryOrDB($clientId);
		if(!$client){
			le("Could not find client with id: $clientId");
		}
		return $client;
	}
}
