<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Resources;

use App\Slim\View\Request\QMRequest;
use App\Types\QMArr;
use App\Types\QMStr;
use Carbon\CarbonInterface;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseJsonResource extends JsonResource
{
    /**
     * Indicates if the resource's collection keys should be preserved.
     *
     * @var bool
     */
    public bool $preserveKeys = true;

    public static function collection($resource): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $resource = QMArr::getUniqueByProperty($resource, 'id');
        return parent::collection($resource);
    }
    public function toArray($request): array {
        $arr = parent::toArray($request);
        if($request->has('include')){
            $include = explode(',', $request->get('include'));
            foreach($include as $i){
                $arr[$i] = $this->$i;
            }
        }
        if($request->has('exclude')){
            $exclude = explode(',', $request->get('exclude'));
            foreach($exclude as $e){
                unset($arr[$e]);
            }
        }
        if($request->get('camel_case')){
            foreach ($arr as $key => $value) {
                $arr[QMStr::camelize($key)] = $value;
                unset($arr[$key]);
            }
        }
        return $arr;
    }

    /**
     * @param array $data
     * @return array
     */
    protected static function removeCountsUnlessRequested(array $data): array
    {
        if (!QMRequest::getQueryParam('with_count')) {
            $data = static::removeCountAttributes($data);
        }
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public static function removeCountAttributes(array $data): array
    {
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'number_of_')) {
                unset($data[$key]);
            }
        }
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public static function removeDateAttributes(array $data): array
    {
        foreach ($data as $key => $value) {
            if (str_ends_with($key, '_at')) {
                unset($data[$key]);
            }
        }
        return $data;
    }
    /**
     * Filter the given data, removing any optional values.
     *
     * @param  array  $data
     * @return array
     */
    protected function filter($data): array
    {
        foreach ($data as $key => $value) {
            if($value instanceof CarbonInterface){
                $data[$key] = db_date($value);
            }
        }
        return parent::filter($data);
    }
}
