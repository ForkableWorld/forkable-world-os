<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UI;
use App\Files\FileHelper;
use App\Traits\ConstantGenerator;
use App\Types\QMStr;
use App\Utils\UrlHelper;
use Parsedown;
class Markdown {
	use ConstantGenerator;
	public const GITHUB = "github";
	public const JENKINS = 'jenkins';
	public const PHP = "php";
	private static $namesBySlug;
	public static function hiddenMD(string $title, string $details): string{
		return "
<details>

<summary>
$title
</summary>

$details

</details>
";
	}
	public static function addIcons(string $str): string{
		$str = str_ireplace("Break Point", ":stop_sign: Break Point", $str);
		$str = str_ireplace("Debug", ":eyes: Debug", $str);
		return $str;
	}
	public static function hiddenListMD(string $title, array $arr): string{
		$str = "";
		foreach($arr as $name => $url){
			if(is_int($name)){
				$str .= "- $url\n";
			} else{
				if(is_array($url)){
					$name = $url['name'];
					$url = $url['url'];
				}
				$str .= "- [$name]($url)\n";
			}
		}
		$md = Markdown::hiddenMD($title, $str);
		return $md;
	}
	public static function toHtml(string $markdown): string{
		$Parsedown = new Parsedown();
		return $Parsedown->text($markdown); # prints: <p>Hello <em>Parsedown</em>!</p>
	}
	/**
	 * @param string $longerSecondaryDescription
	 * @return string
	 */
	public static function collapsibleMarkdownIfNecessary(string $longerSecondaryDescription): string{
		$longerSecondaryDescription = QMStr::stripWhiteSpaceAtTheBeginningOfLines($longerSecondaryDescription);
		if(strlen($longerSecondaryDescription) > 280){
			$summary = QMStr::truncate($longerSecondaryDescription, 70);
			$longerSecondaryDescription = Markdown::hiddenMD($summary, $longerSecondaryDescription);
		}
		return $longerSecondaryDescription;
	}
	public static function badge(string $url, string $subject, string $status, string $color = "red",
		string $logo = "php"){
		$color = QMColor::toString($color);
		$subject = str_replace('-', ' ', $subject);
		$subject = rawurlencode($subject);
		$status = str_replace('-', ' ', $status);
		$status = rawurlencode($status);
		$img = "https://img.shields.io/badge/$subject-$status-$color?style=for-the-badge&logo=$logo";
		UrlHelper::validateUrl($img, "$subject github badge");
		return "[<img src=\"$img\">]($url)";
	}
	public static function toPlainText(string $markdown): string{
		$plain = str_replace("](", "\n\t=> ", $markdown);
		$plain = str_replace("[", "", $plain);
		$plain = str_replace("]", "\n\t", $plain);
		$plain = str_replace(")", "\n\t", $plain);
		$plain = str_replace("<details>", "", $plain);
		$plain = str_replace("</details>", "", $plain);
		$plain = str_replace("</summary>", "=====", $plain);
		$plain = str_replace("<summary>", "=====", $plain);
		$after = null;
		$before = $plain;
		while(true){
			$after = QMStr::removeBetweenAndIncluding(":", ":", $before);
			if($after === $before){
				break;
			}
			$before = $after;
		}
		return $after;
	}
	public static function link(string $text, string $url, bool $lineBreaks = true): string{
		$str = "[$text]($url)";
		if($lineBreaks){
			return "\n$str\n";
		}
		return $str;
	}
	public static function generateConstantName(string $str): string{
		return QMStr::toConstantName(self::$namesBySlug[$str]);
	}
	protected static function generateConstantValues(): array{
		$data = FileHelper::getLineContainingString('vendor/simple-icons/simple-icons/slugs.md', "| `");
		$values = [];
		foreach($data as $line){
			$arr = explode('`', $line);
			$values[] = $arr[3];
			self::$namesBySlug[$values[$arr[3]]] = $values[2];
		}
		return $values;
	}
}
