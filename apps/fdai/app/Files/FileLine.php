<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files;
use App\Types\QMStr;
use Illuminate\Support\Collection;
class FileLine {
	/**
	 * @var int
	 */
	public $number;
	/**
	 * @var UntypedFile
	 */
	protected $file;
	/**
	 * @var mixed|string
	 */
	protected $string;
	/**
	 * FileLine constructor.
	 * @param mixed|string $string
	 * @param int $number
	 * @param UntypedFile $file
	 */
	public function __construct(string $string, int $number, UntypedFile $file){
		$this->string = $string;
		$this->number = $number;
		$this->file = $file;
	}
	/**
	 * @param Collection|array $provided
	 * @param UntypedFile $file
	 * @return array
	 */
	public static function instantiateArray($provided, UntypedFile $file): array{
		$arr = [];
		foreach($provided as $i => $item){
			if(is_string($item)){
				$arr[$i] = new static($item, $i, $file);
			} elseif($item instanceof FileLine){
				$item->number = $i;
				$arr[$i] = $item;
			} else{
				le("What happened?");
			}
		}
		return $arr;
	}
	public function __toString(){ return rtrim($this->string); }
	/**
	 * @return int
	 */
	public function getNumber(): int{
		return $this->number;
	}
	/**
	 * @return UntypedFile
	 */
	public function getFile(): UntypedFile{
		return $this->file;
	}
	public function isTerminated(): bool{
		return $this->getLastCharacter() === ";";
	}
	public function getLastCharacter(): string{
		$trim = $this->getTrimmed();
		return QMStr::getLastCharacter($trim);
	}
	public function replace(string $new): void{
		$this->string = $new;
		$this->getFile()->save();
	}
	public function setString(string $new): void{
		$this->string = $new;
	}
	public function contains(string $needle): bool{
		return strpos($this->string, $needle) !== false;
	}
	public function startsWith(string $needle): bool{
		$str = $this->getTrimmed();
		return strpos($str, $needle) === 0;
	}
	protected function getTrimmed(): string{
		return trim($this->string);
	}
	/**
	 * @param string $dir
	 * @param string $needle
	 * @param bool $recursive
	 * @param string|null $filenameLike
	 * @return FileLine[]
	 */
	public static function getStartingWith(string $dir, string $needle, bool $recursive,
		string $filenameLike = null): array{
		$files = TypedProjectFile::getContaining($dir, $needle, $recursive, $filenameLike);
		$lines = [];
		foreach($files as $file){
			$lines = array_merge($lines, $file->getLinesStartingWith($needle));
		}
		return $lines;
	}
	public function delete(){
		$this->getFile()->deleteLineNumber($this->number);
	}
	public function getBetween(string $start, string $end, string $default = null,
		bool $caseInsensitive = false): string{
		return QMStr::between($this->string, $start, $end, $default, $caseInsensitive);
	}
	public function before(string $string, string $default = null, bool $caseInsensitive = false): ?string{
		return QMStr::before($string, $this->getString(), $default, $caseInsensitive);
	}
	/**
	 * @return string
	 */
	public function getString(): string{
		return $this->string;
	}
	public function after(string $string, string $default = null, bool $caseInsensitive = false): ?string{
		return QMStr::after($string, $this->getString(), $default, $caseInsensitive);
	}
}
