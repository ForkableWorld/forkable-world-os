<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UI;
use App\Exceptions\JavascriptParserException;
use App\Logging\QMLog;
use App\Types\QMStr;
class JavascriptParser {
	/**
	 * @param $str
	 * @param $data
	 * @return false|string|void
	 * @throws JavascriptParserException
	 */
	private static function weird_parse_javascript_object($str, &$data){
		$str = trim($str);
		if(strlen($str) < 1) return;
		if($str[0] != '{'){
			throw new JavascriptParserException('The given string is not a JS object');
		}
		$str = substr($str, 1);
		/* While we have data, and it's not the end of this dict (the comma is needed for nested dicts) */
		while(strlen($str) && $str[0] != '}' && $str[0] != ','){
			/* find the key */
			if($str[0] == "'" || $str[0] == '"'){
				/* quoted key */
				[$str, $key] = self::parse_javascript_data($str, ':');
			} else{
				$match = null;
				/* unquoted key */
				if(!preg_match('/^\s*[a-zA-z_][a-zA-Z_\d]*\s*:/', $str, $match)){
					throw new JavascriptParserException('Invalid key ("' . $str . '")');
				}
				$key = $match[0];
				$str = substr($str, strlen($key));
				$key = trim(substr($key, 0, -1)); /* discard the ':' */
			}
			[$str, $data[$key]] = self::parse_javascript_data($str, '}');
		}
		QMLog::info("Finshed dict. Str: '$str'\n");
		return substr($str, 1);
	}
	/**
	 * @param string $str
	 * @return array
	 * @throws JavascriptParserException
	 */
	private static function parse_javascript_object(string $str): array{
		$data = [];
		$str = trim($str);
		if(strlen($str) < 1){
			throw new JavascriptParserException("No string provided!");
		}
		if($str[0] != '{'){
			throw new JavascriptParserException('The given string is not a JS object');
		}
		$str = substr($str, 1);
		/* While we have data, and it's not the end of this dict (the comma is needed for nested dicts) */
		while(strlen($str) && $str[0] != '}' && $str[0] != ','){
			/* find the key */
			if($str[0] == "'" || $str[0] == '"'){
				/* quoted key */
				[$str, $key] = self::parse_javascript_data($str, ':');
			} else{
				$match = null;
				/* unquoted key */
				if(!preg_match('/^\s*[a-zA-z_][a-zA-Z_\d]*\s*:/', $str, $match)){
					throw new JavascriptParserException('Invalid key ("' . $str . '")');
				}
				$key = $match[0];
				$str = substr($str, strlen($key));
				$key = trim(substr($key, 0, -1)); /* discard the ':' */
			}
			[$str, $data[$key]] = self::parse_javascript_data($str, '}');
		}
		return $data;
	}
	/**
	 * @param $str
	 * @param $term
	 * @return false|int|mixed
	 * @throws JavascriptParserException
	 */
	private static function comma_or_term_pos($str, $term){
		$cpos = strpos($str, ',');
		$tpos = strpos($str, $term);
		if($cpos === false && $tpos === false){
			throw new JavascriptParserException('unterminated dict or array');
		} elseif($cpos === false){
			return $tpos;
		} elseif($tpos === false){
			return $cpos;
		}
		return min($tpos, $cpos);
	}
	/**
	 * @param string $string
	 * @return array
	 * @throws JavascriptParserException
	 */
	public static function parseJavascriptArray(string $string): array{
		$withoutBrackets = QMStr::between($string, "[", "]");
		$objectStrings = explode("},", $withoutBrackets);
		$parsed = [];
		foreach($objectStrings as $objectString){
			$objectString = trim($objectString);
			if(empty($objectString)){
				continue;
			}
			$objectString .= "}";
			$parsed[] = self::parse_javascript_object($objectString);
		}
		return $parsed;
	}
	/**
	 * @param $str
	 * @param string $term
	 * @return array
	 * @throws JavascriptParserException
	 */
	public static function parse_javascript_data($str, string $term = "}"): array{
		$str = trim($str);
		if(is_numeric($str[0] . "0")){
			/* a number (int or float) */
			$newpos = self::comma_or_term_pos($str, $term);
			$num = trim(substr($str, 0, $newpos));
			$str = substr($str, $newpos + 1); /* discard num and comma */
			if(!is_numeric($num)){
				throw new JavascriptParserException('OOPSIE while parsing number: "' . $num . '"');
			}
			return [trim($str), $num + 0];
		} elseif($str[0] == '"' || $str[0] == "'"){
			/* string */
			$q = $str[0];
			$offset = 1;
			do {
				$pos = strpos($str, $q, $offset);
				$offset = $pos;
			} while($str[$pos - 1] == '\\'); /* find un-escaped quote */
			$data = substr($str, 1, $pos - 1);
			$str = substr($str, $pos);
			$pos = self::comma_or_term_pos($str, $term);
			$str = substr($str, $pos + 1);
			return [trim($str), $data];
		} elseif($str[0] == '{'){
			/* dict */
			$data = [];
			$str = self::weird_parse_javascript_object($str, $data);
			return [$str, $data];
		} elseif($str[0] == '['){
			/* array */
			$arr = [];
			$str = substr($str, 1);
			while(strlen($str) && $str[0] != $term && $str[0] != ','){
				$val = null;
				[$str, $val] = self::parse_javascript_data($str, ']');
				$arr[] = $val;
				$str = trim($str);
			}
			$str = trim(substr($str, 1));
			return [$str, $arr];
		} elseif(stripos($str, 'true') === 0){
			/* true */
			$pos = self::comma_or_term_pos($str, $term);
			$str = substr($str, $pos + 1); /* discard terminator */
			return [trim($str), true];
		} elseif(stripos($str, 'false') === 0){
			/* false */
			$pos = self::comma_or_term_pos($str, $term);
			$str = substr($str, $pos + 1); /* discard terminator */
			return [trim($str), false];
		} elseif(stripos($str, 'null') === 0){
			/* null */
			$pos = self::comma_or_term_pos($str, $term);
			$str = substr($str, $pos + 1); /* discard terminator */
			return [trim($str), null];
		} elseif(strpos($str, 'undefined') === 0){
			/* null */
			$pos = self::comma_or_term_pos($str, $term);
			$str = substr($str, $pos + 1); /* discard terminator */
			return [trim($str), null];
		} else{
			throw new JavascriptParserException('Cannot figure out how to parse "' . $str . '" (term is ' . $term .
				')');
		}
	}
}
