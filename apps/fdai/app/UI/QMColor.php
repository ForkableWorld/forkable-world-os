<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UI;
use App\Traits\ConstantSearch;
class QMColor {
	use ConstantSearch;
	public const ADMIN_LTE_AQUA = 'aqua';
	public const ADMIN_LTE_BLUE = self::STRING_BLUE;
	public const ADMIN_LTE_FUCHSIA = 'fuchsia';
	public const ADMIN_LTE_GREEN = self::STRING_GREEN;
	public const ADMIN_LTE_LIGHT_BLUE = 'light-blue';
	public const ADMIN_LTE_LIME = 'lime';
	public const ADMIN_LTE_MAROON = 'maroon';
	public const ADMIN_LTE_NAVY = 'navy';
	public const ADMIN_LTE_OLIVE = 'olive';
	public const ADMIN_LTE_PURPLE = self::STRING_PURPLE;
	public const ADMIN_LTE_RED = self::STRING_RED;
	public const ADMIN_LTE_TEAL = 'teal';
	public const ADMIN_LTE_YELLOW = self::STRING_YELLOW;
	public const BOOTSTRAP_DANGER = 'danger';
	public const BOOTSTRAP_DARK = 'dark';
	public const BOOTSTRAP_INFO = 'info';
	public const BOOTSTRAP_WHITE = 'white';
	public const TRANSPARENT = 'transparent';
	public const BOOTSTRAP_MUTED = 'muted';
	public const BOOTSTRAP_PRIMARY = 'primary';
	public const BOOTSTRAP_SECONDARY = 'secondary';
	public const BOOTSTRAP_SUCCESS = 'success';
	public const BOOTSTRAP_WARNING = 'warning';
	public const HEX_ARMY_GREEN = '#5ba082';
	public const HEX_BLUE = self::HEX_GOOGLE_BLUE;
	public const HEX_CYAN = '#17a2b8';
	public const HEX_DARK_BLUE = '#2E294E';
	public const HEX_DARK_GRAY = '#2b3138';
	public const HEX_FACEBOOK_BLUE = '#3b5998';
	public const HEX_FUCHSIA = self::HEX_PURPLE;
	public const HEX_GOOGLE_BLUE = "#3467d6";
	public const HEX_GOOGLE_GREEN = "#0f9d58";
	public const HEX_GOOGLE_PLUS_RED = '#d34836';
	public const HEX_GOOGLE_RED = "#dd4b39";
	public const HEX_GOOGLE_YELLOW = "#f09402";
	public const HEX_GREEN = self::HEX_GOOGLE_GREEN;
	public const HEX_INDIGO = '#6610f2';
	public const HEX_LIGHT_BLUE = "#f46523";
	public const HEX_LIGHT_GREEN = "#66ff00";
	public const HEX_LIGHT_GRAY = '#d9e2ef';
	public const HEX_LIGHT_RED = "#ff2a26";
	public const HEX_ORANGE = self::HEX_GOOGLE_YELLOW;
	public const HEX_PINK = '#e83e8c';
	public const HEX_PURPLE = '#886aea';
	public const HEX_RED = self::HEX_GOOGLE_RED;
	public const HEX_WHITE = '#ffffff';
	public const HEX_BLACK = '#000000';
	public const HEX_TEAL = '#20c997';
	public const HEX_TWITTER_BLUE = '#00aced';
	public const HEX_YELLOW = self::HEX_GOOGLE_YELLOW;
	public const STRING_BLUE = 'blue';
	public const STRING_BLACK = 'black';
	public const STRING_WHITE = 'white';
	public const STRING_DARK_GRAY = 'darkgray';
	public const STRING_LIGHT_GRAY = 'lightgray';
	public const STRING_GREEN = 'green';
	public const STRING_ORANGE = 'orange';
	public const STRING_PINK = 'pink';
	public const STRING_PURPLE = 'purple';
	public const STRING_RED = 'red';
	public const STRING_YELLOW = 'yellow';
	public const STRING_TO_BOOTSTRAP = [
		self::STRING_RED => self::BOOTSTRAP_DANGER,
		self::STRING_ORANGE => self::BOOTSTRAP_WARNING,
		self::STRING_BLUE => self::BOOTSTRAP_PRIMARY,
		self::STRING_PURPLE => self::BOOTSTRAP_PRIMARY,
		self::STRING_GREEN => self::BOOTSTRAP_SUCCESS,
		self::ADMIN_LTE_FUCHSIA => self::BOOTSTRAP_DANGER,
		self::ADMIN_LTE_AQUA => self::BOOTSTRAP_INFO,
		self::STRING_YELLOW => self::BOOTSTRAP_WARNING,
		self::STRING_DARK_GRAY => self::BOOTSTRAP_DARK,
	];
	public const STRING_TO_HEX = [
		self::STRING_RED => self::HEX_RED,
		self::STRING_ORANGE => self::HEX_ORANGE,
		self::STRING_BLUE => self::HEX_BLUE,
		self::STRING_PURPLE => self::HEX_PURPLE,
		self::STRING_GREEN => self::HEX_GREEN,
		self::ADMIN_LTE_FUCHSIA => self::HEX_RED,
		self::ADMIN_LTE_AQUA => self::HEX_LIGHT_BLUE,
		self::STRING_YELLOW => self::HEX_YELLOW,
		self::STRING_DARK_GRAY => self::HEX_DARK_GRAY,
	];
	public const HEX_TO_BOOTSTRAP = [
		self::HEX_LIGHT_GREEN => self::BOOTSTRAP_SUCCESS,
		self::HEX_BLACK => self::BOOTSTRAP_DARK,
		self::HEX_ARMY_GREEN => self::BOOTSTRAP_SUCCESS,
		self::HEX_BLUE => self::BOOTSTRAP_PRIMARY,
		self::HEX_DARK_BLUE => self::BOOTSTRAP_PRIMARY,
		self::HEX_DARK_GRAY => self::BOOTSTRAP_DARK,
		self::HEX_FACEBOOK_BLUE => self::BOOTSTRAP_PRIMARY,
		self::HEX_FUCHSIA => self::BOOTSTRAP_DANGER,
		self::HEX_GOOGLE_BLUE => self::BOOTSTRAP_PRIMARY,
		self::HEX_GOOGLE_GREEN => self::BOOTSTRAP_SUCCESS,
		self::HEX_GOOGLE_PLUS_RED => self::BOOTSTRAP_DANGER,
		self::HEX_GOOGLE_RED => self::BOOTSTRAP_DANGER,
		self::HEX_GOOGLE_YELLOW => self::BOOTSTRAP_WARNING,
		self::HEX_GREEN => self::BOOTSTRAP_SUCCESS,
		self::HEX_LIGHT_BLUE => self::BOOTSTRAP_PRIMARY,
		self::HEX_LIGHT_RED => self::BOOTSTRAP_DANGER,
		self::HEX_LIGHT_GRAY => self::BOOTSTRAP_MUTED,
		self::HEX_PURPLE => self::BOOTSTRAP_PRIMARY,
		self::HEX_TWITTER_BLUE => self::BOOTSTRAP_INFO,
		self::HEX_YELLOW => self::BOOTSTRAP_WARNING,
		self::HEX_CYAN => self::BOOTSTRAP_INFO,
		self::HEX_INDIGO => self::BOOTSTRAP_PRIMARY,
		self::HEX_PINK => self::BOOTSTRAP_WARNING,
		self::HEX_TEAL => self::BOOTSTRAP_INFO,
		self::HEX_WHITE => self::BOOTSTRAP_WHITE,
	];
	public const HEX_TO_STRING = [
		self::HEX_LIGHT_GREEN => self::STRING_GREEN,
		self::HEX_BLACK => self::STRING_BLACK,
		self::HEX_ARMY_GREEN => self::STRING_GREEN,
		self::HEX_BLUE => self::STRING_BLUE,
		self::HEX_DARK_BLUE => self::STRING_BLUE,
		self::HEX_DARK_GRAY => self::STRING_DARK_GRAY,
		self::HEX_FACEBOOK_BLUE => self::STRING_BLUE,
		self::HEX_GREEN => self::STRING_GREEN,
		self::HEX_LIGHT_BLUE => self::STRING_BLUE,
		self::HEX_LIGHT_RED => self::STRING_RED,
		self::HEX_PURPLE => self::STRING_PURPLE,
		self::HEX_TWITTER_BLUE => self::STRING_BLUE,
		self::HEX_YELLOW => self::STRING_YELLOW,
		self::HEX_GOOGLE_PLUS_RED => self::STRING_RED,
		self::HEX_RED => self::STRING_RED,
		self::HEX_CYAN => self::STRING_BLUE,
		self::HEX_INDIGO => self::STRING_PURPLE,
		self::HEX_LIGHT_GRAY => self::STRING_DARK_GRAY,
		self::HEX_PINK => self::STRING_PINK,
		self::HEX_TEAL => self::STRING_BLUE,
		self::HEX_WHITE => self::STRING_WHITE,
	];
	public const STRING_TO_GRADIENT = [
		self::STRING_PINK => ['#ec407a', '#d81b60'],
		self::STRING_ORANGE => ['#ef5350', '#e53935'],
		self::STRING_YELLOW => ['#ffa726', '#fb8c00'],
		self::STRING_GREEN => ['#66bb6a', '#43a047'],
		self::STRING_PURPLE => ['#ab47bc', '#8e24aa'],
		self::ADMIN_LTE_FUCHSIA => ['#ab47bc', '#8e24aa'],
		self::STRING_BLUE => ['#26c6da', '#00acc1'],
		self::STRING_RED => ['#ef5350', '#e53935'],
	];
	private static $lastRandomDark = 0;
	private static $lastRandomHex = 0;
	private static $lastRandomString = 0;
	public static function resetRandomColors(){
		self::$lastRandomDark = 0;
		self::$lastRandomHex = 0;
		self::$lastRandomString = 0;
	}
	/**
	 * @param float $percent
	 * @return string
	 */
	public static function gradeToColor(float $percent): string{
		switch(true) {
			case $percent > 90:
				return self::HEX_RED;
			case $percent > 80:
				return self::HEX_GOOGLE_YELLOW;
			case $percent > 70:
				return self::HEX_GOOGLE_GREEN;
			case $percent > 60:
				return self::HEX_GOOGLE_BLUE;
		}
		return self::HEX_DARK_GRAY;
	}
	public static function toGradient(string $color): array{
		$color = self::toString($color);
		if(!isset(self::STRING_TO_GRADIENT[$color])){
			throw new \LogicException("Please define STRING_TO_GRADIENT for $color in " . static::class);
		}
		return self::STRING_TO_GRADIENT[$color];
	}
	public static function toHex(string $colorStr): string{
		if(strpos($colorStr, "#") === 0){
			return $colorStr;
		}
		$colorStr = self::toString($colorStr);
		if(!isset(self::STRING_TO_HEX[$colorStr])){
			throw new \LogicException("Please set STRING_TO_HEX for $colorStr");
		}
		return self::STRING_TO_HEX[$colorStr];
	}
	public static function getStringColors(): array{
		$colors = [];
		$const = static::getConstants();
		foreach($const as $name => $value){
			if(is_string($value) && strpos($name, 'STRING_') === 0){
				$colors[] = $value;
			}
		}
		return array_values(array_unique($colors));
	}
	public static function randomStringColor(): string{
		$colors = self::getStringColors();
		$color = $colors[self::$lastRandomString];
		if(self::$lastRandomString === (count($colors) - 1)){
			self::$lastRandomString = 0;
		} else{
			self::$lastRandomString++;
		}
		return $color;
	}
	public static function randomHexColor(): string{
		$colors = self::getHexColors();
		$color = $colors[self::$lastRandomHex];
		if(self::$lastRandomHex === (count($colors) - 1)){
			self::$lastRandomHex = 0;
		} else{
			self::$lastRandomHex++;
		}
		return $color;
	}
	public static function randomDarkHexColor(): string{
		$colors = self::getDarkHexColors();
		$color = $colors[self::$lastRandomDark];
		if(self::$lastRandomDark === (count($colors) - 1)){
			self::$lastRandomDark = 0;
		} else{
			self::$lastRandomDark++;
		}
		return $color;
	}
	public static function toString(string $hex): string{
		$arr = self::getStringColors();
		if(in_array($hex, $arr)){
			return $hex;
		}
		if(!isset(self::HEX_TO_STRING[$hex])){
			le('!isset(self::HEX_TO_STRING[$hex]),"Please set HEX_TO_STRING for $hex"');
		}
		return self::HEX_TO_STRING[$hex];
	}
	public static function getHexColors(): array{
		$const = static::getConstants();
		$hex = [];
		foreach($const as $name => $value){
			if(is_string($value) && strpos($value, "#") === 0){
				$hex[$name] = $value;
			}
		}
		return array_values(array_unique($hex));
	}
	public static function getDarkHexColors(): array{
		return [
			//self::HEX_GOOGLE_PLUS_RED,
			self::HEX_GOOGLE_GREEN,
			self::HEX_GOOGLE_BLUE,
			self::HEX_ARMY_GREEN,
			self::HEX_GOOGLE_RED,
			self::HEX_GOOGLE_YELLOW,
			self::HEX_PURPLE,
			self::HEX_PINK,
		];
	}
	public static function getBootstrap(): array{
		$const = static::getConstants();
		$hex = [];
		foreach($const as $name => $value){
			if(is_string($value) && strpos($name, "BOOTSTRAP_") === 0){
				$hex[$name] = $value;
			}
		}
		return $hex;
	}
	public static function toBootstrap(string $value): string{
		$bootstrap = self::getBootstrap();
		if(in_array($value, $bootstrap)){
			return $value;
		}
		if(strpos($value, "#") === 0){
			if(!isset(self::HEX_TO_BOOTSTRAP[$value])){
				throw new \LogicException("Please set HEX_TO_BOOTSTRAP for $value");
			}
			return self::HEX_TO_BOOTSTRAP[$value];
		}
		$value = self::toString($value);
		if(!isset(self::STRING_TO_BOOTSTRAP[$value])){
			throw new \LogicException("Please set STRING_TO_BOOTSTRAP for $value");
		}
		return self::STRING_TO_BOOTSTRAP[$value];
	}
	public static function profitToColor(float $profit, float $min = -100, float $max = 100): string{
		if($profit > 0){
			$r = 0;
			if($max == 0){
				$max = 0.0000000000001;
			}
			$g = round(155 * $profit / $max + 100);
		} else{
			if($min == 0){
				$min = -0.0000000000001;
			}
			$r = round(155 * $profit / $min + 100);
			$g = 0;
		}
		///$alpha = abs($profit)/100;
		$alpha = 1;
		if($alpha !== 1){
			return 'rgb(' . $r . ',' . $g . ",0,$alpha)";
		} // PHPStorm says alpha not allowed?
		return 'rgb(' . $r . ',' . $g . ",0)";
	}
	public static function isWhite(string $color): bool{
		return in_array($color, [self::HEX_WHITE, self::STRING_WHITE, self::BOOTSTRAP_WHITE]);
	}
}
