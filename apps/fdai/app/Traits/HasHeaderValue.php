<?php

namespace App\Traits;

trait HasHeaderValue
{
	abstract protected static function getHeaderNames(): array;
	/**
	 * @return string|null
	 */
	protected static function fromHeader(): ?string
	{
		$headerNames = static::getHeaderNames();
		$headers = request()->headers;
		foreach($headerNames as $headerName){
			if($val = $headers->get($headerName)){
				return $val;
			}
		}
		$screaming = static::getScreamingHeaderNames();
		foreach ($screaming as $headerName){
			if (isset($_SERVER[$headerName])){
				return $_SERVER[$headerName];
			}
		}
		return null;
	}
	protected static array $screamingHeaderNames = [];
	private static function getScreamingHeaderNames(): array
	{
		if(isset(static::$screamingHeaderNames[static::class])){
			return static::$screamingHeaderNames[static::class];
		}
		$headerNames = static::getHeaderNames();
		$screamingHeaderNames = [];
		foreach($headerNames as $headerName){
			$screamingHeaderNames[] = strtoupper("HTTP_".str_replace('-', '_', $headerName));
		}
		return static::$screamingHeaderNames[static::class] = $screamingHeaderNames;
	}
}
