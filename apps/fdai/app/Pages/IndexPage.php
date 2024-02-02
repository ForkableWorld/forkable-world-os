<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Pages;
use App\Models\BaseModel;
use Illuminate\View\View;
abstract class IndexPage extends BasePage {
	/**
	 * @return BaseModel
	 */
	abstract public static function getClass(): string;
	public function getUrl(): string{
		$class = static::getClass();
		return $class::indexUrl();
	}
	public function getView(): View{
		$class = static::getClass();
		return $class::getIndexPageView();
	}
	public function getTitleAttribute(): string{
		$class = static::getClass();
		return $class::getClassNameTitlePlural();
	}
	public function getSubtitleAttribute(): string{
		$class = static::getClass();
		return $class::getClassDescription();
	}
	public function getImage(): string{
		$class = static::getClass();
		return $class::getClassImage();
	}
	public function getFontAwesome(): string{
		$class = static::getClass();
		return $class::getClassFontAwesome();
	}
	public function getIcon(): string{
		$class = static::getClass();
		return $class::getClassImage();
	}
	public function getContentView(): View{
		$class = static::getClass();
		return $class::getIndexPageView();
	}
	public function getKeyWords(): array{
		$class = static::getClass();
		return $class::getClassKeywords();
	}
}
