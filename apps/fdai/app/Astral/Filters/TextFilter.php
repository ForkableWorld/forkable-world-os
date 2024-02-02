<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Filters;
use App\Types\QMStr;
use dddeeemmmooonnn\AstralMulticolumnFilter\AstralMulticolumnFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class TextFilter extends AstralMulticolumnFilter {
	//public $component = 'multicolumn-text-filter';
	protected $default_column_type = 'text';
	public function __construct($columns = null, $manual_update = null, $default_column_type = null, $name = null){
		$columns = collect($columns)->sortBy(function($arr){
			return $arr['label'];
		})->all();
		parent::__construct($columns, $manual_update, $default_column_type, $name);
	}
	public function apply(Request $request, $query, $value){
		$columns = $this->getOptions();
		foreach(json_decode($value, true) as $val){
			$val['value'] = urldecode($val['value']);
			$val['operator'] = urldecode($val['operator']);
			if(!$val['operator'] && in_array($columns[$val['column']]['type'], ['select', 'checkbox'])){
				$val['operator'] = '=';
			}
			if(!$val['column'] || !$val['operator'] || $val['value'] === ''){
				continue;
			}
			if(strtolower($val['operator']) === 'like'){
				$val['value'] = '%' . $val['value'] . '%';
			}
			$value = $val['value'];
			$op = $val['operator'];
			$col = $columns[$val['column']]['column'];
			$type = $columns[$val['column']]['type'];
			if($columns[$val['column']]['apply']){
				$query = $this->{$columns[$val['column']]['apply']}($query, $col, $op, $value);
			} elseif($type === 'date'){
				$query = $query->where($col, $op, $value);
			} else{
				if(QMStr::isNullString($value)){
					$query = $query->whereNull($col);
				} elseif(strtolower($value) === "not null"){
					$query = $query->whereNotNull($col);
				} else{
					$query = $query->where($col, $op, $value);
				}
			}
		}
		return $query;
	}
	private function getOptions(): array{
		$columns = [];
		foreach($this->columns as $column => $value){
			if(is_string($value)){
				$value = [];
			}
			if(!isset($value['label'])){
				$value['label'] = str_replace('_', ' ', Str::title(Str::snake($column, '_')));
			}
			if(!isset($value['type'])){
				$value['type'] = $this->default_column_type;
			}
			if($value['type'] === 'select'){
				if(!isset($value['options']) || !$value['options']){
					le("Column $column has no options in " . get_class($this));
				}
				if(is_string($value['options'])){
					$method = 'options' . ucfirst($value['options']);
					if(method_exists($this, $method)){
						$value['options'] = $this->restructureArray($this->$method());
					} else{
						le("Method $method not exists in " . get_class($this));
					}
				} else{
					$value['options'] = $this->restructureArray($value['options']);
				}
			} elseif($value['type'] !== 'checkbox'){
				if(!isset($value['operators']) || !$value['operators']){
					$value['operators'] = $this->restructureArray($this->operatorsDefault());
				} elseif(is_string($value['operators'])){
					$method = 'operators' . ucfirst($value['operators']);
					if(method_exists($this, $method)){
						$value['operators'] = $this->restructureArray($this->$method());
					} else{
						le("Method $method not exists in " . get_class($this));
					}
				} else{
					$value['operators'] = $this->restructureArray($value['operators']);
				}
			}
			if(!isset($value['column'])){
				$value['column'] = $column;
			}
			if(!isset($value['placeholder'])){
				$value['placeholder'] = $value['label'];
			}
			if(isset($value['apply'])){
				$method = 'apply' . ucfirst($value['apply']);
				if(method_exists($this, $method)){
					$value['apply'] = $method;
				} else{
					le("Method $method not exists in " . get_class($this));
				}
			} else{
				$value['apply'] = false;
			}
			$value['value'] = $column;
			$columns[$column] = $value;
		}
		return $columns;
	}
	private function restructureArray($array): array{
		$return = [];
		foreach($array as $value => $label){
			$return[] = [
				'label' => $label,
				'value' => urlencode($value),
			];
		}
		return $return;
	}
}
