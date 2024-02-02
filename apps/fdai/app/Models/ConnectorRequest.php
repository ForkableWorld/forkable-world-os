<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\CodeGenerators\CodeGenerator;
use App\Files\FileHelper;
use App\Files\Json\JsonToPhpFile;
use App\Files\MimeContentTypeHelper;
use App\Models\Base\BaseConnectorRequest;
use App\Traits\HasModel\HasDataSource;
use App\Traits\HasModel\HasUser;
use App\Types\QMStr;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * App\Models\ConnectorRequest
 * @property int $id
 * @property int $connector_id
 * @property int $user_id
 * @property int $connection_id
 * @property int $connector_import_id
 * @property string $method
 * @property int $code
 * @property string $uri
 * @property mixed $response_body
 * @property mixed $request_body
 * @property array $request_headers
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property Connection $connection
 * @property ConnectorImport $connector_import
 * @property Connector $connector
 * @property User $user
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|ConnectorRequest newModelQuery()
 * @method static Builder|ConnectorRequest newQuery()
 * @method static Builder|ConnectorRequest query()
 * @method static Builder|ConnectorRequest whereCode($value)
 * @method static Builder|ConnectorRequest whereConnectionId($value)
 * @method static Builder|ConnectorRequest whereConnectorId($value)
 * @method static Builder|ConnectorRequest whereConnectorImportId($value)
 * @method static Builder|ConnectorRequest whereCreatedAt($value)
 * @method static Builder|ConnectorRequest whereDeletedAt($value)
 * @method static Builder|ConnectorRequest whereId($value)
 * @method static Builder|ConnectorRequest whereMethod($value)
 * @method static Builder|ConnectorRequest whereRequestBody($value)
 * @method static Builder|ConnectorRequest whereRequestHeaders($value)
 * @method static Builder|ConnectorRequest whereResponseBody($value)
 * @method static Builder|ConnectorRequest whereUpdatedAt($value)
 * @method static Builder|ConnectorRequest whereUri($value)
 * @method static Builder|ConnectorRequest whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $content_type
 * @property string|null $imported_data_from_at Earliest data that we've requested from this data source
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|ConnectorRequest whereContentType($value)
 * @method static Builder|ConnectorRequest whereImportedDataFromAt($value)
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class ConnectorRequest extends BaseConnectorRequest {
    use HasFactory;

	use HasDataSource;
	use HasUser;
	use SearchesRelations;

	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = 'id';
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		'id',
	];
	public static $group = Connection::CLASS_CATEGORY;
	/**
	 * The number of results to display in the global search.
	 * @var int
	 */
	public static $globalSearchResults = 10;
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'connector' => ['name'],
	];
	public const METABASE_PATH = ConnectorImport::METABASE_PATH;
	public const CLASS_DESCRIPTION = "An API request made to an HTTP endpoint during import from a data source. ";
	const CLASS_CATEGORY = Connection::CLASS_CATEGORY;
	/**
	 * @var array The relationships that should always be loaded.
	 */
	protected $with = [
		//        'user',
		//'connector' // Too complicated and redundant data. Just get relations directly
	];
	protected array $openApiSchema = [
		self::FIELD_REQUEST_HEADERS => ['type' => 'array', 'items' => ['type' => 'string']],
	];
	protected $casts = [
		self::FIELD_CODE => 'int',
		self::FIELD_CONNECTION_ID => 'int',
		self::FIELD_CONNECTOR_ID => 'int',
		self::FIELD_CONNECTOR_IMPORT_ID => 'int',
		self::FIELD_ID => 'int',
		self::FIELD_METHOD => 'string',
		self::FIELD_REQUEST_HEADERS => 'array',
		self::FIELD_URI => 'string',
		self::FIELD_USER_ID => 'int',
		//self::FIELD_RESPONSE_BODY => 'string', // Sometimes html and sometimes json
		//self::FIELD_REQUEST_BODY => 'json',
	];
	protected array $rules = [
		self::FIELD_CONNECTOR_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_CONNECTION_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_CONNECTOR_IMPORT_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_METHOD => 'required|max:10',
		self::FIELD_CODE => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_URI => 'required|max:2083',
		//self::FIELD_RESPONSE_BODY => 'nullable|max:65535',
		//self::FIELD_REQUEST_BODY => 'nullable|max:65535',
		//self::FIELD_REQUEST_HEADERS => 'required|max:65535'
	];

	public function generateModelCodeAndFixturesForResponse(){
		$this->generateFixtureFromResponse();
		//$this->saveSerializedRequest($request);
		//$this->generateModelCode();
	}
	/**
	 * @param $request
	 */
	public function saveSerializedRequest($request){
		$path = $this->getFixturePath() . ".serialized";
		JsonToPhpFile::writeJsonFileByPath($path, serialize($request));
	}
	protected function generateFixtureFromResponse(){
		$path = $this->getFixturePath();
		$body = $this->response_body;
		if(is_string($body) && stripos($body, "<html>") !== false){
			FileHelper::writeHtmlFile($path, $body);
		} else{
			JsonToPhpFile::writeJsonFileByPath($path, $body);
		}
	}
	protected function getDataSourceClassName(): string{
		$connectorClass = $this->getQMDataSource()->getTitleAttribute();
		return QMStr::toClassName($connectorClass);
	}
	protected function getResponseFullClassName(): string{
		return $this->getQMDataSource()->uriPathToResponseClass($this->uri);
	}
	/**
	 * @return string
	 */
	private function getResponsesNameSpace(): string{
		$connectorClass = $this->getQMDataSource()->getTitleAttribute();
		$connectorClass = QMStr::toClassName($connectorClass);
		return "App\DataSources\Connectors\Responses\\$connectorClass";
	}
	/**
	 * @return string
	 */
	public function getResponsesFolderForConnector(): string{
		$folder = $this->getQMDataSource()->getResponsesFolder();
		return $folder;
	}
	/**
	 * @return string
	 */
	public function getResponseClass(): string{
		$folder = $this->getQMDataSource()->getResponsesFolder();
		return $folder;
	}
	/**
	 * @return void
	 */
	public function generateResponseClassWithJson2PHP(): void{
		CodeGenerator::jsonToBaseModel($this->getResponseFullClassName(), $this->response_body);
	}
	/**
	 * @return string
	 */
	protected function getFixturePath(): string{
		$folder = $this->getResponsesFolder();
		$url = QMStr::afterLast($this->uri, "//");
		$path = $folder . '/' . $url;
		$path = str_replace('~/', '', $path);
		$path = str_replace('-/', '', $path);
		return $path;
	}
	/** @noinspection PhpUnused */
	/**
	 * @param $value
	 */
	public function setResponseBodyAttribute($value){
		if(empty($value)){
			return;
		}
		if(is_string($value)){
			$body = $this->attributes[self::FIELD_RESPONSE_BODY] = $value;
		} else{
			$body = $this->attributes[self::FIELD_RESPONSE_BODY] = json_encode($value);
		}
		if(stripos($body, '<html') !== false){
			$this->attributes['content_type'] = MimeContentTypeHelper::HTML;
		} else{
			$this->attributes['content_type'] = MimeContentTypeHelper::JSON;
		}
	}
	/** @noinspection PhpUnused */
	/**
	 * @return mixed|string
	 */
	public function getResponseBodyAttribute(){
		$body = $this->attributes[self::FIELD_RESPONSE_BODY] ?? $this->getUrl();
		if(stripos($body, '<html') !== false){
			return $body;
		} else{
			return QMStr::decodeIfJson($body);
		}
	}
	public function getSubtitleAttribute(): string{
		if(!$this->hasId()){
			return static::CLASS_DESCRIPTION;
		}
		return $this->getQMConnector()->getSubtitleAttribute();
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{ return true; }
	//	public function setRequestBodyAttribute($value){
	//		$this->attributes[self::FIELD_REQUEST_BODY]  = QMStr::jsonEncodeIfNecessary($value);
	//	}
}
