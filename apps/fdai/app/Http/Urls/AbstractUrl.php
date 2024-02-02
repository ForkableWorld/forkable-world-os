<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Urls;
use App\Utils\UrlHelper;
use Spatie\Url\QueryParameterBag;
use Spatie\Url\Url;
abstract class AbstractUrl extends Url {
	/** @var string */
	protected $scheme = 'https';
	/** @var string */
	protected $host = '';
	/** @var int|null */
	protected $port = null;
	/** @var string */
	protected $user = '';
	/** @var string|null */
	protected $password = null;
	/** @var string */
	protected $path = '';
	/** @var QueryParameterBag */
	protected $query;
	/** @var string */
	protected $fragment = '';
	public function __construct(){
		parent::__construct();
		$this->path = $this->generatePath();
		$this->host = $this->generateHost();
	}
	abstract protected function generatePath(): string;
	protected function generateHost(): string{ return \App\Utils\Env::getAppUrl(); }
	public static function get(): string{
		return static::create()->__toString();
	}
	public static function send(): void{
		$url = UrlHelper::addParams(self::get(), $_GET);
		UrlHelper::redirect($url);
	}
}
