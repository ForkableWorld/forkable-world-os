<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Pages;
use App\Buttons\QMButton;
use App\Menus\QMMenu;
use App\Storage\S3\S3Public;
use App\Traits\HasClassName;
use App\Types\QMStr;
use App\Utils\UrlHelper;
use Illuminate\View\View;
abstract class BasePage {
	use HasClassName;
	abstract public function getUrl(): string;
	abstract public function getView(): View;
	public function getSideMenu(): ?QMMenu{ return null; }
	public function getTopMenu(): ?QMMenu{ return null; }
	abstract public function getTitleAttribute(): string;
	abstract public function getSubtitleAttribute(): string;
	abstract public function getImage(): string;
	abstract public function getFontAwesome(): string;
	abstract public function getIcon(): string;
	public function getViewParams(array $params = []): array{
		$params['sideMenu'] = $this->getSideMenu();
		$params['topMenu'] = $this->getSideMenu();
		$params['title'] = $this->getTitleAttribute();
		$params['body'] = $this->getBody();
		$params['page'] = $this;
		return $params;
	}
	public function getHtml(): string{
		$v = $this->getView();
		try {
			return $v->render();
		} catch (\Throwable $e) {
			le($e);
			/** @var \LogicException $e */
			throw $e;
		}
	}
	public function getBody(): string{
		$v = $this->getContentView();
		try {
			return $v->render();
		} catch (\Throwable $e) {
			le($e);
			/** @var \LogicException $e */
			throw $e;
		}
	}
	abstract public function getContentView(): View;
	public function getButton(): QMButton{
		$b = new QMButton();
		$b->setTextAndTitle($this->getTitleAttribute());
		$b->setTooltip($this->getSubtitleAttribute());
		$b->setUrl($this->getUrl());
		return $b;
	}
	public function uploadToS3(){
		S3Public::uploadHTML($this->getS3Path(), $this->getHtml());
	}
	public function getS3Path(): string{
		return 'pages/' . $this->getSlug();
	}
	public function getSlug(): string{
		return QMStr::slugify($this->getPath());
	}
	public function getPath(): string{
		return UrlHelper::toPath($this->getUrl());
	}
	public function getKeyWordString(): string{
		$keywords = $this->getKeyWords();
		return QMStr::generateKeyWordString($keywords);
	}
	abstract public function getKeyWords(): array;
}
