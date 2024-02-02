<?php
namespace App\Http\Parameters;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;

class SearchParam extends BaseParam
{
    public static function getSynonyms(): array{
        return [
            'search', 'q', 'query', 'searchPhrase'
        ];
    }
    public static function get(bool $throwException = false, $default = null){
        $search = parent::get($throwException, $default);
        if (!$search) {
            $path = request()->getPathInfo();
            if($search = QMStr::after('/search/', $path, null)){
                $search = QMStr::before('?', $search, $search);
            }
        }
        return $search;
    }
}
