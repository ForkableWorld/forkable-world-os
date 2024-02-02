<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\WpPost;
use App\Models\WpPost;
use App\Traits\PropertyTraits\WpPostProperty;
use App\Properties\Base\BasePostTitleProperty;
use App\Types\QMStr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LogicException;
class WpPostPostTitleProperty extends BasePostTitleProperty
{
    use WpPostProperty;
    const FILTER_MISSING_SPACE = 'missing_space';
    public $table = WpPost::TABLE;
    public $parentClass = WpPost::class;
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $post = $this->getWpPost();
		if(!$post){le('!$post');}
        $title = $post->post_title;
        if($title === $post->post_name){
            le("Title should not equal post name but is ".$post->post_name ." in ".
                \App\Logging\QMLog::print_r($post->toArray(), true));
        }
        if($title === $post->post_excerpt){
            le("Title should not equal post_excerpt but is ".$post->post_excerpt ." in ".
                \App\Logging\QMLog::print_r($post->toArray(), true));
        }
    }
    public static function fixInvalidRecords(){
        $noTitle = static::whereQMQB("NOT LIKE", '% %')->get();
        /** @var WpPost $post */
        foreach($noTitle as $post){
            $post->logInfo("$post->post_title $post->post_name");
        }
    }
    /**
     * Apply the filter to the given query.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $type
     * @param Request|null $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyFilters($query, $type, Request $request = null): Builder {
        if($type === self::FILTER_MISSING_SPACE){
            $this->applyWhere($query, "NOT LIKE", '% %');
        }
        return $query;
    }
    /**
     * Get the filter's available options.
     * @param Request|null $request
     * @return array
     */
    public function invalidRecordOptions(Request $request = null){
        return [
            QMStr::titleCaseSlow(self::FILTER_MISSING_SPACE) => self::FILTER_MISSING_SPACE
        ];
    }
}
