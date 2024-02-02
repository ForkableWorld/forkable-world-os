<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Bash;
use App\Files\FileLine;
use App\Files\Traits\IsMergableFile;
use Illuminate\Support\Collection;
use Tests\QMAssert;
class BashLibScriptFile extends BashScriptFile {
	use IsMergableFile;
	public $sourceLine;
	private ?BashScriptFile $sourcingFile;
	/**
	 * @param null $file
	 * @param null $sourceLine
	 * @param BashScriptFile|null $sourcingFile
	 */
	public function __construct($file = null, $sourceLine = null, BashScriptFile $sourcingFile = null){
		$this->sourcingFile = $sourcingFile;
		$this->sourceLine = $sourceLine;
		parent::__construct($file);
	}
	public static function getFolderPaths(): array{
		return [
			self::getDefaultFolderRelative(),
			//'scripts/bsfl/lib',
		];
	}
	/**
	 * @param array $folders
	 * @param string|null $pathNotLike
	 * @return Collection|static[]
	 */
	public static function get(array $folders = [], string $pathNotLike = null): Collection{
		$notLike = ['/bats', '/examples/'];
		if($pathNotLike){
			$notLike[] = $pathNotLike;
		}
		$all = parent::get($folders, $pathNotLike);
		return $all->filter(function(BashScriptFile $one) use ($notLike){
			foreach($notLike as $needle){
				if(stripos($one, $needle) !== false){
					return null;
				}
			}
			return $one;
		});
	}
	public static function getDefaultFolderRelative(): string{
		return parent::getDefaultFolderRelative() . "/lib";
	}
	/**
	 * @return BashScriptFile
	 */
	public function getSourcingFile(): BashScriptFile{
		return $this->sourcingFile;
	}
	public function getSourceLine(): string{
		$relative = $this->getRelativePath();
		$dots = $this->getDotsPathToRoot();
		if($this->getFileName() === "bsfl.sh"){
			QMAssert::assertEquals("../../", $dots);
		}
		return "# shellcheck source=./$dots$relative
source \$QM_API/$relative";
	}
	/**
	 * @return BashLibScriptFile[]
	 */
	protected function getExistingSourcedScripts(): array{
		$scripts = [];
		$lines = $this->getLinesContaining("source ");
		/** @var FileLine[] $lines */
		foreach($lines as $line){
			$path = $line->after('$QM_API/');
			$scripts[] = new BashLibScriptFile($path, $line, $this);
		}
		return $scripts;
	}
	public static function getFilePathsToMerge(): array{
		return static::getPaths();
	}
	public static function getMergedOutputFilePath(): string{
		return static::getDefaultFolderRelative() . "/merged_functions.sh";
	}
}
