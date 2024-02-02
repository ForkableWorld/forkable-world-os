<?php
namespace App\Http\Urls;
use App\Parameters\StateParameter;
use App\Slim\View\Request\QMRequest;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
class FinalCallbackUrl extends AbstractUrl {
	public const FINAL_CALLBACK_URL = 'final_callback_url';
	public const NAME = self::FINAL_CALLBACK_URL;
    public static function save(){
		$val = (new static())->generatePath();
		if($val){
			session()->set(self::FINAL_CALLBACK_URL, $val);
		}
	}
	protected function generatePath(): string{
		return static::getIfSet();
	}
	public static function getIfSet(): ?string {
		$url = QMRequest::getParam(self::FINAL_CALLBACK_URL);
		if(!$url){
			try {
				$url = session()->get(self::FINAL_CALLBACK_URL);
			} catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
				le($e);
			}
		}
		if(!$url){
			$url = StateParameter::getValueFromStateParam(self::FINAL_CALLBACK_URL);
		}
		return $url;
	}
    public static function set(?string $finalCallback){
	    \Session::put(self::FINAL_CALLBACK_URL, $finalCallback);
    }
}
