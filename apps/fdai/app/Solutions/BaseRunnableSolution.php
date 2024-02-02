<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Buttons\QMButton;
use App\Http\Urls\AdminUrl;
use App\Logging\QMLog;
use App\Traits\FileTraits\IsSolution;
use App\Traits\HasClassName;
use App\Types\QMStr;
use App\UI\Markdown;
use App\Utils\UrlHelper;
use Facade\IgnitionContracts\RunnableSolution;
use Facade\IgnitionContracts\Solution;
abstract class BaseRunnableSolution extends AbstractSolution implements RunnableSolution {
	use IsSolution;
	use HasClassName;
	public static function dumpSolution(Solution $s): string{
		\App\Logging\ConsoleLog::info(
			$msg = "SOLUTION:\n".$s->getSolutionTitle()."\n".$s->getSolutionDescription());
		foreach($s->getDocumentationLinks() as $name => $url){
			$msg .= QMLog::linkButton($name, $url);
		}
		return $msg;
	}
	/**
	 * @param Solution $s
	 * @return string
	 * Keep this static so we can render non-QM solutions
	 */
	public static function solutionToMarkdown(Solution $s): string{
		$md = "\n### Solution: ".$s->getSolutionTitle()."\n";
		$md .= "\n".$s->getSolutionDescription()."\n";
		$links = $s->getDocumentationLinks();
		foreach($links as $name => $url){
			if(!is_string($name)){
				QMLog::print($name, "name");
			}
			if(!is_string($url)){
				$links = $s->getDocumentationLinks();
				QMLog::print($url, "url");
			}
			if(!$url){
				QMLog::error("no url for $name! All links:".\App\Logging\QMLog::print_r($links, true));
				continue;
			}
			if(is_array($url)){
				foreach($url as $key => $value){
					$md .= Markdown::link($key, $value);
				}
			} else{
				$md .= Markdown::link($name, $url);
			}
		}
		return $md;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public static function runAndRedirect(array $params = []): string{
		$class = $params['solutionClass'] ?? $params['class'] ?? null;
		if(!$class){
			le("Please provide solutionClass param!");
		}
		$s = self::instantiate($class);
		$s->run($params);
		return UrlHelper::redirect($s->getRedirectUrl());
	}
	/**
	 * @param $class
	 * @return BaseRunnableSolution
	 */
	public static function instantiate($class): BaseRunnableSolution{
		$class = QMStr::toShortClassName($class);
		$full = self::getNamespace().'\\'.$class;
		$s = new $full();
		return $s;
	}
	public function getButton(): QMButton{
		$b = new QMButton();
		$b->setTextAndTitle($this->getRunButtonText());
		$b->setUrl($this->getRunUrl());
		return $b;
	}
	public function getRunButtonText(): string{
		return $this->getSolutionTitle();
	}
	public function getRunUrl(): string{
		$params = $this->getRunParameters();
		$params['solutionClass'] = $this->getFullClassName();
		return UrlHelper::getLocalUrl('admin/solution', $params);
	}
	public function getRunParameters(): array{
		return [];
	}
	public function renderString(): string{
		$str = $this->getSolutionTitle()."\n";
		$str .= $this->getSolutionActionDescription()."\n";
		$str .= $this->getRunButtonText()."\n";
		$str .= $this->getRunUrl()."\n";
		return $str;
	}
	public function getSolutionActionDescription(): string{
		return $this->getSolutionDescription();
	}
	public function toArray(): array{
		return json_decode(json_encode($this), true);
	}
	public function getUrl(): string{
		$params = $this->getRunParameters();
		$params['class'] = self::getShortClassName();
		return AdminUrl::getAdminUrl('solution', $params);
	}
	public function getDocumentationLinks(): array{
		return $this->getRunLink();
	}
	public function getRunLink(): array{
		return [$this->getRunButtonText() => $this->getRunUrl()];
	}
}
