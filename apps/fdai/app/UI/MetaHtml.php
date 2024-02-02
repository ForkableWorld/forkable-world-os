<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UI;
use App\Logging\QMLog;
use Illuminate\Support\Str;
use App\AppSettings\AdditionalSettings\AppIds;
use App\AppSettings\HostAppSettings;
use App\Reports\AnalyticalReport;
use App\Slim\View\Request\QMRequest;
class MetaHtml {
	/**
	 * @var string
	 */
	public $title;
	/**
	 * @var string
	 */
	public $description;
	/**
	 * @var string
	 */
	public $image;
	/**
	 * @var string
	 */
	public $url;
	/**
	 * HtmlHelper constructor.
	 * @param string $title
	 * @param string $description
	 * @param string $image
	 * @param string $url
	 */
	public function __construct(string $title, string $description, string $image, string $url){
		$this->title = $title;
		$description = HtmlHelper::stripHtmlTags($description);
		if(str_contains($description, '"')){
			QMLog::error( "Meta description cannot contain double quote marks but is $description so replacing with single quotes");
			$description = str_replace('"', "'", $description);
		}
		$this->description = $description;
		$this->image = $image;
		$this->url = $url;
		return $this->getSocialMetaHtml();
	}
	/**
	 * @param string|AnalyticalReport $title
	 * @param $description
	 * @param $image
	 * @param $url
	 * @return string
	 */
	public static function generateSocialMetaHtml($title, string $description = null, string $image = null,
		string $url = null): string{
		if(is_object($title)){
			$obj = $title;
			$title = $obj->getTitleAttribute();
			$description = $obj->getSubtitleAttribute();
			$image = $obj->getImage();
			$url = $obj->getUrl();
		} elseif(is_array($title)){
			$title = $title["title"];
			if(!$image){
				$image = $title["image"] ?? null;
			}
		}
		if(!$title){
			$title = app_display_name();
		}
		if(!$description){
			$description = html_meta_description();
		}
		if(!$image){
			$image = html_meta_image();
		}
		if(!$url){
			$url = home_page();
		}
		$metaHtml = new MetaHtml($title, $description, $image, $url);
		return $metaHtml->getSocialMetaHtml();
	}
	/**
	 * @return string
	 */
	private function getGoogleMetaHtml(): string{
		return '
<!--Google+ Metadata /-->
<meta itemprop="name" content="' . $this->title . '">
<meta itemprop="description" content="' . Str::limit($this->description, $limit = 100, $end = '...') . '"/>
<meta itemprop="image" content="' . $this->image . '"/>
        ';
	}
	/**
	 * @return string
	 */
	private function getTwitterMetaHtml(): string{
		$description = $this->getSubtitleAttribute();
		$image = $this->getImage();
		$url = $this->getUrl();
		$title = $this->getTitleAttribute();
		return "
<!-- Twitter Metadata -->
<meta name=\"twitter:card\" content=\"summary\">
<meta name=\"twitter:site\" content=\"@quantimodo\">
<meta name=\"twitter:title\" content=\"$title\">
<meta name=\"twitter:description\" content=\"$description\">
<meta name=\"twitter:image\" content=\"$image\"/>
<meta name=\"twitter:domain\" content=\"$url\">
        ";
	}
	/**
	 * @return string
	 */
	private function getFacebookMetaHtml(): string{
		if($mem = HostAppSettings::fromMemory()){
			$id = $mem->getAppIds()->getFacebookAppId();
		} else{
			$id = AppIds::DEFAULT_FACEBOOK_APP_ID;
		}
		$description = $this->getSubtitleAttribute();
		$image = $this->getImage();
		$url = $this->getUrl();
		$title = $this->getTitleAttribute();
		$html = "
<!--Facebook Metadata -->
<meta property=\"og:type\" content=\"website\">
<meta property=\"og:url\" content=\"$url\">
<meta property=\"fb:app_id\" content=\"$id\">
<meta property=\"og:image\" content=\"$image\">
<meta property=\"og:description\" content=\"$description\">
<meta property=\"og:title\" content=\"$title\">
        ";
		//            @if(!empty($meta[\'imageWidth\']))
		//                <meta property="og:image:width" content="'.$meta[\'imageWidth\'].'"/>
		//            @endif
		//            @if(!empty($meta[\'imageHeight\']))
		//                <meta property="og:image:height" content="'.$meta[\'imageHeight\'].'"/>
		//            @endif
		return $html;
	}
	/**
	 * @return string
	 */
	public function getSocialMetaHtml(): string{
		return $this->getGoogleMetaHtml() . $this->getTwitterMetaHtml() . $this->getFacebookMetaHtml();
	}
	/**
	 * @return string
	 */
	public function getSubtitleAttribute(): string{
		return $this->description;
	}
	/**
	 * @return string
	 */
	public function getImage(): string{
		return $this->image;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		return $this->url;
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		return $this->title;
	}
	public static function generateTitle(): string{
		return QMRequest::getTitleAttribute();
	}
	public static function generateImage(): string{
		return QMRequest::getImage();
	}
	public static function generateDescription(): string{
		return QMRequest::getSubtitleAttribute();
	}
	public static function getKeywordString(): string{
		return QMRequest::getKeywordString();
	}
}
