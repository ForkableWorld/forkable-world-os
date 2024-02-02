<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\QMButton;
use App\Exceptions\InvalidStringException;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Types\QMArr;
use App\UI\HtmlHelper;
use Torann\LaravelMetaTags\Facades\MetaTag;
trait HasButton {
	abstract public function getFontAwesome(): string;
	abstract public function getTitleAttribute(): string;
	abstract public function getSubtitleAttribute(): string;
	abstract public function getUrl(array $params = []): string;
	/**
	 * @return string
	 */
	abstract public function getImage(): string;
	public function logUrl(){
		QMLog::linkButton("View " . $this->getTitleAttribute(), $this->getUrl());
	}
	public function setHtmlMetaTags(){
		MetaTag::set('title', $this->getTitleAttribute());
		MetaTag::set('description', $this->getSubtitleAttribute());
        try {
            MetaTag::set('image', $this->getImage());
        } catch (InvalidStringException $e) {
            QMLog::error("Could not set meta image because".$e->getMessage());
        }
    }
	//public function getTooltip():string{return $this->getSubtitleAttribute();}
	/**
	 * @return QMButton
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getButton(){ return static::toButton($this); }
	/**
	 * @param BaseModel[] $models
	 * @return QMButton[]
	 */
	public static function toButtons($models): array{
		$buttons = [];
		foreach($models as $model){
			$b = $model->getButton();
			$buttons[$b->getTitleAttribute()] = $b;
		}
		QMArr::sortDescending($buttons, 'badgeText');
		return $buttons;
	}
	/**
	 * @param HasButton $obj
	 * @return QMButton
	 */
	protected static function toButton($obj): QMButton{
		$b = new QMButton();
		$b->setTextAndTitle($obj->getTitleAttribute());
		$b->setTooltip($obj->getSubtitleAttribute());
		$b->setUrl($obj->getUrl());
		$b->setFontAwesome($obj->getFontAwesome());
		$b->setImage($obj->getImage());
		$b->setBackgroundColor($obj->getColor());
		return $b;
	}
	public function getChipSmall(): string{
		return HtmlHelper::renderView(view('small-tailwind-chip', ['button' => $this]));
	}
	public function getChipMedium(): string{
		return HtmlHelper::renderView(view('tailwind-chip-medium', ['button' => $this]));
	}
	// https://www.creative-tim.com/learning-lab/tailwind-starter-kit/landing
	public function getTailwindCardWithIconCircle(): string{
		return HtmlHelper::renderView(view('tailwind-card-with-circled-icon', ['button' => $this]));
	}
	/**
	 * @param string $color
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getRoundOutlineWithIcon(string $color = "indigo"): string{
		return "
<a href=\"{$this->getUrl()}\" title=\"{$this->getSubtitleAttribute()}\">
    <button class=\"round-outline-with-icon-button text-$color-500 bg-transparent border border-solid border-$color-500 hover:bg-$color-500 hover:text-white active:bg-$color-600 font-bold uppercase px-1 py-1 rounded-full outline-none focus:outline-none mr-1 mb-1\"
    type=\"button\"
    style=\"transition: all .15s ease\">
        <i class=\"{$this->getFontAwesome()}\"></i>&nbsp;&nbsp;{$this->getTitleAttribute()}
    </button>
</a>
";
	}
}
