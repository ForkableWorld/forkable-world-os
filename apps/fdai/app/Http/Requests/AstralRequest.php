<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Requests;
use App\Logging\QMLog;
use App\Astral\Filters\PeopleFilter;
use App\Astral\BaseAstralAstralResource;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Traits\HasClassName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use App\MemoizesMethods;
/**
 * @property bool editing
 * @property string editMode
 * @property string relationshipType
 */
class AstralRequest extends FormRequest
{
    use HasClassName;
    use InteractsWithResources, InteractsWithRelatedResources, MemoizesMethods;
	/**
	 * @param array $query
	 * @param array $request
	 * @param array $attributes
	 * @param array $cookies
	 * @param array $files
	 * @param array $server
	 * @param null $content
	 */
	public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [],
		array $files = [], array $server = [], $content = null){
		parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
	}
	/**
     * @return mixed
     */
    protected static function getRelationshipName(): ?string {
        $rel = AstralRequest::req()->request->get("viaRelationship");
        return $rel;
    }
    /**
     * Determine if this request is via a many to many relationship.
     *
     * @return bool
     */
    public function viaManyToMany(): bool {
        return in_array(
            $this->relationshipType,
            ['belongsToMany', 'morphToMany']
        );
    }
    /**
     * Determine if this request is an attach or create request.
     *
     * @return bool
     */
    public function isCreateOrAttachRequest(): bool {
        $editing = $this->editing;
        $mode = $this->editMode;
        $inArr = in_array($mode, ['create', 'attach']);
        return $editing && $inArr;
    }
    /**
     * Determine if this request is an update or update-attached request.
     *
     * @return bool
     */
    public function isUpdateOrUpdateAttachedRequest(): bool {
        return $this->editing && in_array($this->editMode, ['update', 'update-attached']);
    }
    /**
     * @return AstralRequest
     * Not sure how to get access to ResourceIndexRequest otherwise
     */
    public static function req(): AstralRequest {
        if ($mem = Memory::get(Memory::NOVA_REQUEST)) {
            return $mem;
        }
        return BaseAstralAstralResource::getAstralRequest();
    }
    public static function isUpdate(): bool {
        $request = static::req();
        return $request instanceof UpdateResourceRequest || $request->isUpdateOrUpdateAttachedRequest();
    }
    public static function isCreate(): bool {
        $request = static::req();
        return $request instanceof CreateResourceRequest || $request->isCreateOrAttachRequest();
    }
    public static function isAssociatableSearch(): bool{
        return QMRequest::urlContains("/associatable/");
    }
    public static function isCreateOrAssociatableSearch(): bool{
        return self::isCreate() || self::isAssociatableSearch();
    }
    public static function isStandardIndex(): bool {
        $request = static::req();
        if (self::isUpdate() || self::isCreateOrAssociatableSearch() || self::isDetail()) {
            return false;
        }
        if ($request instanceof ResourceIndexRequest) {  // AssociatableSearch is a ResourceIndexRequest
            return true;
        }
        if ($request->request->get("resourceId")) {
            QMLog::error("What kind of request is this?", $request->request->all());
            return false; // Not sure what this is?
            // https://local.quantimo.do/astral-api/tracking-reminders/associatable/user?current=230&first=true&search=&withTrashed=false&resourceId=142612&viaResource=&viaResourceId=&viaRelationship=local.quantimo.do/astral-api/tracking-reminders/associatable/user?current=230&first=true&search=&withTrashed=false&resourceId=142612&viaResource=&viaResourceId=&viaRelationship=
        }
        return true; // I guess this is default?
    }
    public static function isDetail(): bool {
        $request = static::req();
        return $request instanceof ResourceDetailRequest;
    }
    /**
     * @param string $relationship
     * @return bool Returns the name of the relationship method
     * true if being listed as index on a related details page
     */
    public static function forRelationshipTable(string $relationship): bool {
        $rel = self::getRelationshipName();
        return $rel === $relationship;
    }
    public static function relationshipType(): ?string {
        return AstralRequest::req()->request->get("relationshipType");
    }
    public static function hasMany(): bool {
        return self::relationshipType() === "hasMany";
    }
    public static function isViaRelationship(): bool {
        return self::getRelationshipName() !== null;
    }
    public static function shouldShowAnyFilters(): bool {
        $r = self::req();
        if($r instanceof ResourceIndexRequest){
            return !$r->viaRelationship();  // Already filtered by relationship
        } else {
            return true;
        }
    }
    /**
     * @param Request $request
     * Not sure how to get access to ResourceIndexRequest otherwise
     */
    public static function setInMemory(Request $request){
        Memory::set(Memory::NOVA_REQUEST, $request);
    }
    /**
     * @return bool
     */
    public static function filterIsEveryone(): bool {
        return self::filterIs(PeopleFilter::EVERYONE);
    }
    /**
     * @param string $value
     * @return bool
     */
    public static function filterIs(string $value): bool {
        foreach (ResourceIndexRequest::getApplyFilters() as $applyFilter) {
            if ($applyFilter->value === $value) {return true;}
        }
        return false;
    }
    /**
     * @return \App\Query\ApplyFilter[]
     */
    public static function getApplyFilters(): array{
        $request = self::req();
        if(!method_exists($request, 'filters')){return [];}
        /** @var ResourceIndexRequest $request */
        return $request->filters()->all();
    }
}
