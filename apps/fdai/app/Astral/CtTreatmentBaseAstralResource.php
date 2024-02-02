<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral;
use Illuminate\Http\Request;
use Titasgailius\SearchRelations\SearchesRelations;
use App\Models\CtTreatment;
class CtTreatmentBaseAstralResource extends BaseAstralAstralResource
{
    use SearchesRelations;  // TODO: Comment if you don't need to search relations
    /**
     * The model the resource corresponds to.
     * @var string
     */
    public static $model = CtTreatment::class;
    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        // TODO: uncomment if you don't need to search relations 'id',
    ];
    public static $group = CtTreatment::CLASS_CATEGORY;
    public static $searchRelations = [
        // TODO add relationships to search here or uncomment regular search above i.e. 'connector' => [Connector::FIELD_DISPLAY_NAME],
    ];
    /**
     * Get the searchable columns for the resource.
     *
     * @return array
     */
    public static function searchableColumns(): array{
        $parent = parent::searchableColumns();
        return []; // Prevents returning id field
    }
    /**
     * The per-page options used the resource index.
     *
     * @var array
     */
    public static $perPageOptions = [10, 25, 50, 100];
    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = [
        // TODO add relationships here i.e. 'user',
        // TODO add relationships here i.e. 'connector'
    ];
}
