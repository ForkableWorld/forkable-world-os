<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\ModelTraits;
use App\DataSources\ConnectInstructions;
use App\DataSources\QMConnector;
use App\Files\FileHelper;
use App\Models\Connector;
use App\Storage\S3\S3Public;
use App\Traits\HardCodable;
use App\Traits\HasName;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\Utils\UrlHelper;
use Illuminate\View\View;
trait ConnectorTrait {
	use HardCodable, HasName;
	public function getDeletedAt(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Connector::FIELD_DELETED_AT] ?? null;
		} else{
			/** @var QMConnector $this */
			return $this->deletedAt;
		}
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(Connector::FIELD_DELETED_AT, $deletedAt);
	}
	public function setDisplayName(string $displayName): void{
		$this->setAttribute(Connector::FIELD_DISPLAY_NAME, $displayName);
	}
	public function setEnabled(bool $enabled): void{
		$this->setAttribute(Connector::FIELD_ENABLED, $enabled);
	}
	public function getGetItUrl(): ?string{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Connector::FIELD_GET_IT_URL] ?? null;
		} else{
			/** @var QMConnector $this */
			return $this->getItUrl;
		}
	}
	public function setGetItUrl(string $getItUrl): void{
		$this->setAttribute(Connector::FIELD_GET_IT_URL, $getItUrl);
	}
	public function setImage(string $image): void{
		$this->setAttribute(Connector::FIELD_IMAGE, $image);
	}
	public function getIsPublic(): ?bool{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Connector::FIELD_IS_PUBLIC] ?? null;
		} else{
			/** @var QMConnector $this */
			return $this->isPublic;
		}
	}
	public function setIsPublic(bool $isPublic): void{
		$this->setAttribute(Connector::FIELD_IS_PUBLIC, $isPublic);
	}
	public function setLongDescription(string $longDescription): void{
		$this->setAttribute(Connector::FIELD_LONG_DESCRIPTION, $longDescription);
	}
	public function setName(string $name): void{
		$this->setAttribute(Connector::FIELD_NAME, $name);
	}
	public function getNumberOfConnections(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Connector::FIELD_NUMBER_OF_CONNECTIONS] ?? null;
		} else{
			/** @var QMConnector $this */
			return $this->numberOfConnections;
		}
	}
	public function setNumberOfConnections(int $numberOfConnections): void{
		$this->setAttribute(Connector::FIELD_NUMBER_OF_CONNECTIONS, $numberOfConnections);
	}
	public function getNumberOfConnectorImports(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Connector::FIELD_NUMBER_OF_CONNECTOR_IMPORTS] ?? null;
		} else{
			/** @var QMConnector $this */
			return $this->numberOfConnectorImports;
		}
	}
	public function setNumberOfConnectorImports(int $numberOfConnectorImports): void{
		$this->setAttribute(Connector::FIELD_NUMBER_OF_CONNECTOR_IMPORTS, $numberOfConnectorImports);
	}
	public function getNumberOfConnectorRequests(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Connector::FIELD_NUMBER_OF_CONNECTOR_REQUESTS] ?? null;
		} else{
			/** @var QMConnector $this */
			return $this->numberOfConnectorRequests;
		}
	}
	public function setNumberOfConnectorRequests(int $numberOfConnectorRequests): void{
		$this->setAttribute(Connector::FIELD_NUMBER_OF_CONNECTOR_REQUESTS, $numberOfConnectorRequests);
	}
	public function getNumberOfMeasurements(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Connector::FIELD_NUMBER_OF_MEASUREMENTS] ?? null;
		} else{
			/** @var QMConnector $this */
			return $this->numberOfMeasurements;
		}
	}
	public function setNumberOfMeasurements(?int $numberOfMeasurements): void{
		$this->setAttribute(Connector::FIELD_NUMBER_OF_MEASUREMENTS, $numberOfMeasurements);
	}
	public function getOauth(): ?bool{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Connector::FIELD_OAUTH] ?? null;
		} else{
			/** @var QMConnector $this */
			return $this->oauth;
		}
	}
	public function setOauth(bool $oauth): void{
		$this->setAttribute(Connector::FIELD_OAUTH, $oauth);
	}
	public function getQmClient(): ?bool{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Connector::FIELD_QM_CLIENT] ?? null;
		} else{
			/** @var QMConnector $this */
			return $this->qmClient;
		}
	}
	public function setQmClient(bool $qmClient): void{
		$this->setAttribute(Connector::FIELD_QM_CLIENT, $qmClient);
	}
	public function setShortDescription(string $shortDescription): void{
		$this->setAttribute(Connector::FIELD_SHORT_DESCRIPTION, $shortDescription);
	}
	public function getWpPostId(): ?int{
		if(property_exists($this, 'attributes') && $this->attributes){
			return $this->attributes[Connector::FIELD_WP_POST_ID] ?? null;
		} else{
			/** @var QMConnector $this */
			return $this->wpPostId;
		}
	}
	public function setWpPostId(int $wpPostId): void{
		$this->setAttribute(Connector::FIELD_WP_POST_ID, $wpPostId);
	}
	public function getSortingScore(): float{
		return $this->getNumberOfConnections();
	}
	public static function getS3Bucket(): string{
		return S3Public::getBucketName();
	}
	public function getKeyWords(): array{
		return [$this->getTitleAttribute(), $this->getTitleAttribute() . " Import"];
	}
	public function getShowContentView(array $params = []): View{
		return view('connector', $this->getShowParams($params));
	}
	protected function getShowPageView(array $params = []): View{
		return view('connector', $this->getShowParams($params));
	}
	protected function getEditView(array $params = []): View{
		return view('connector-credentials', ['connector' => $this->l()]);
	}
	public function getEditHTML(array $params = []): string{
		return HtmlHelper::renderView($this->getEditView($params));
	}
	public function getAvatar(): string{
		return $this->getImage();
	}
	public function getBadgeText(): ?string{
		return null;
	}
	/**
	 * @return string
	 */
	public static function getHardCodedDirectory(): string{
		return FileHelper::absPath("app/DataSources/Connectors");
	}
	protected function generateFileContentOfHardCodedModel(): string{
		// TODO: Implement generateFileContentOfHardCodedModel() method.
	}
	protected function getHardCodedShortClassName(): string{
		return QMStr::toShortClassName($this->getTitleAttribute()) . "Connector";
	}
	public function getIcon(): string{
		return $this->getImage();
	}
	/**
	 * @return ConnectInstructions
	 */
	public function getConnectInstructions(): ?ConnectInstructions{
		return $this->getQMConnector()->getConnectInstructions();
	}
	public function getConnectUrlWithParams(): string{
		return UrlHelper::getUrl("api/v1/connectors/" . $this->getNameAttribute()) . "/connect";
	}
}
