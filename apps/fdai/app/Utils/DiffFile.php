<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Exceptions\DiffException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Files\UntypedFile;
use App\Folders\DynamicFolder;
use App\Logging\QMLog;
use App\Repos\QMAPIRepo;
use App\Storage\S3\S3Private;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use Jfcherng\Diff\DiffHelper;
use Tests\UpdateHtmlTestFixturesTest;
class DiffFile extends UntypedFile {
	const SideBySide = 'SideBySide';
	/**
	 * @var string
	 */
	public $fixturePath;
	/**
	 * @var string
	 */
	public $inlineUrl;
	/**
	 * @var string
	 */
	public $sideBySideUrl;
	private $actualRaw;
	/**
	 * @var string
	 */
	private $actualUrl;
	private bool $ignoreNumbers;
	/**
	 * @param        $actualRaw
	 * @param string $pathToFixture
	 * @param bool $ignoreNumbers
	 */
	public function __construct($actualRaw, string $pathToFixture, bool $ignoreNumbers){
        //if(strpos($pathToFixture, ".diff") === false){$pathToFixture .= ".diff";}
		$this->actualRaw = $actualRaw;
		$this->fixturePath = FileHelper::toRelativePath($pathToFixture);
		$this->ignoreNumbers = $ignoreNumbers;
		parent::__construct(FileHelper::absPath($pathToFixture));
	}
    protected function validateExtension(string $schemaPath): void
    { // TODO: This is variable
        //parent::validateExtension($schemaPath);
    }

    /**
	 * @param string $html
	 * @return string
	 */
	public static function stripCSRF(string $html): string{
		$html = QMStr::replace_between_and_including($html, '<meta name="csrf-token" content="', '">',
			'<!--CSRF_WAS_HERE-->');
		$html = QMStr::replace_between_and_including($html, 'window.Laravel = {"csrfToken":"', '"}</script>',
			'<!--CSRF_WAS_HERE-->');
		return $html;
	}
	public static function getDefaultFolder(): string{
		return DynamicFolder::STORAGE . "/" . static::getDefaultExtension();
	}
	public static function getDefaultExtension(): string{
		return "diff";
	}
	/**
	 * @throws DiffException|QMFileNotFoundException
	 */
	public function assertSame(string $message = null): void{
		$this->saveIfLocal();
		if($this->getActualNormalized() !== $this->getExpectedNormalized()){
			//throw new DiffException($this, $message);
			if(!EnvOverride::isLocal()){ // We don't throw exception locally to allow test to run to generate all changed
				// artifacts, so we don't have to re-run it for every single file when multiple are produced by one test
				throw new DiffException($this, $message);
			}

		}
	}
	/**
	 * @return string
	 */
	public function getActualNormalized(): string{
		return $this->normalize($this->actualRaw);
	}
	/**
	 * @param string|object $data
	 * @return string
	 */
	public function normalize($data): string{
		if(!is_string($data)){
			$data = json_decode(json_encode($data), true);
			QMArr::alphabetizeKeysRecursive($data);
			$str = QMStr::prettyJsonEncode($data);
		} else{
			$str = $data;
		}
		$str = self::stripLivewireToken($str); // This must come first or random sections get replaced by IgnoreNumbers
		if($this->getIgnoreNumbers()){
			$str = QMStr::removeDatesTimesAndNumbers($str);
		} else{
			$str = QMStr::removeDatesAndTimes($str);
		}
		$str = HtmlHelper::trimWhitespace($str);
		$str = QMStr::removeEmptyLines($str);
		$str = DiffFile::replaceFeatureWithTestingUrl($str);
		return $str;
	}
	/**
	 * @param string $html
	 * @return string
	 */
	public static function stripLivewireToken(string $html): string{
		$html = QMStr::replace_between_and_including($html, 'window.livewire_token = ', ';', '<!--CSRF_WAS_HERE-->');
		return $html;
	}
	/**
	 * @return bool
	 */
	public function getIgnoreNumbers(): bool{
		return $this->ignoreNumbers;
	}
	/**
	 * @return string
	 * @throws QMFileNotFoundException
	 */
	private function getExpectedNormalized(): string{
		try {
			return $this->normalize($this->getExpectedRaw());
		} catch (QMFileNotFoundException $e) {
			if(EnvOverride::isLocal()){ // Just keep going, so we can generate all necessary fixtures for test if local
				$this->saveIfLocal();
				return $this->normalize($this->getExpectedRaw());
			}
			$test = AppMode::getCurrentTestName();
			$path = $this->getFixturePath();
			throw new QMFileNotFoundException($path,
				"No previous $path to compare to!\n\tRun $test and commit $path if it looks good.");
		}
	}
	/**
	 * @return string|null
	 * @throws QMFileNotFoundException
	 */
	public function getExpectedRaw(): string{
		$old = FileHelper::getContents($this->getFixturePath());
		return $old;
	}
	/**
	 * @return string
	 */
	public function getFixturePath(): string{
		return $this->fixturePath;
	}
	private function saveIfLocal(): void{
		if(EnvOverride::isLocal()){
			$this->save();
		}
	}
	public function getContents(): string{
		return $this->getActualRawWithoutWhiteSpaceOrLiveWire() ?? parent::getContents();
	}
	/**
	 * @return string
	 */
	public function getActualRawWithoutWhiteSpaceOrLiveWire(): string{
		$str = HtmlHelper::trimWhitespace($this->getActualRaw());
		$str = self::stripLivewireToken($str);
		$str = self::replaceFeatureWithTestingUrl($str);
		return $str;
	}
	/**
	 * @return string
	 */
	public function getActualRaw(): string{
		return $this->actualRaw;
	}
	/**
	 * @return string
	 * @throws QMFileNotFoundException
	 */
	public function getInlineUrl(): string{
		$expected = $this->getExpectedNormalized();
		$actual = $this->getActualNormalized();
		if(!$this->inlineUrl){
			$this->inlineUrl = self::inline($expected, $actual, $this->fixturePath);
		}
		return $this->inlineUrl;
	}
	/**
	 * @param mixed $expected
	 * @param mixed $actual
	 * @param string|null $path
	 * @return string
	 */
	public static function inline($expected, $actual, string $path = null): string{
		return self::generateDiffUrl($expected, $actual, $path, 'Inline');
	}
	/**
	 * @param mixed $expected
	 * @param mixed $actual
	 * @param string|null $s3Path
	 * @param string $renderer SideBySide or Inline
	 * @return string
	 */
	public static function generateDiffUrl($expected, $actual, string $s3Path, string $renderer = self::SideBySide): 
	string{
		$rendererOptions = [
			// how detailed the rendered HTML is? (line, word, char)
			'detailLevel' => 'line', // renderer language: eng, cht, chs, jpn, ...
			// or an array which has the same keys with a language file
			'language' => 'eng', // show line numbers in HTML renderers
			'lineNumbers' => true, // show a separator between different diff hunks in HTML renderers
			'separateBlock' => true,
			// the frontend HTML could use CSS "white-space: pre;" to visualize consecutive whitespaces
			// but if you want to visualize them in the backend with "&nbsp;", you can set this to true
			'spacesToNbsp' => false, // HTML renderer tab width (negative = do not convert into spaces)
			'tabSize' => 4, // this option is currently only for the Json renderer.
			// internally, ops (tags) are all int type but this is not good for human reading.
			// set this to "true" to convert them into string form before outputting.
			'outputTagAsString' => false,
			// change this value to a string as the returned diff if the two input strings are identical
			'resultForIdenticals' => null, // extra HTML classes added to the DOM of the diff container
			'wrapperClasses' => ['diff-wrapper'],
		];
		// options for Diff class
		$diffOptions = [
			// show how many neighbor lines
			'context' => 1, // ignore case difference
			'ignoreCase' => false, // ignore whitespace difference
			'ignoreWhitespace' => false,
		];
		// demo the no-inline-detail diff
		$html = DiffHelper::calculate(QMStr::print($expected), QMStr::print($actual), $renderer, $diffOptions,
			['detailLevel' => 'none'] + $rendererOptions);
		$html = "<style type=\"text/css\">" . DiffHelper::getStyleSheet() . "</style>
            $html";
		$s3Path = "diffs/$s3Path-$renderer.html";
		$s3Path = str_replace('//', '/', $s3Path);
		$s3Path = str_replace('\\', '/', $s3Path);
		$url = S3Private::uploadHTML($s3Path, $html, false);
		$url = UrlHelper::getLocalUrl($url);
		$title = QMStr::pathToTitle($s3Path);
		QMLog::logLocalLinkButton($url, "$renderer DIFF of $title");
		return $url;
	}
	/**
	 * @return string
	 * @throws QMFileNotFoundException
	 */
	public function getSideBySideUrl(): string{
		$expected = $this->getExpectedNormalized();
		$actual = $this->getActualNormalized();
		if(!$this->sideBySideUrl){
			$this->sideBySideUrl = self::sideBySide($expected, $actual, $this->fixturePath);
		}
		return $this->sideBySideUrl;
	}
	/**
	 * @param mixed $expected
	 * @param mixed $actual
	 * @param string|null $path
	 * @return string
	 */
	public static function sideBySide($expected, $actual, string $path = null): string{
		return self::generateDiffUrl($expected, $actual, $path, 'SideBySide');
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public function getMessage(): string{
		$expected = $this->getExpectedNormalized();
		$actual = $this->getActualNormalized();
		if($expected === $actual){
			return "Both are the same";
		}
		$inlineUrl = $this->getInlineUrl();
		$sideBySideUrl = $this->getSideBySideUrl();
		return "
INLINE DIFF: $inlineUrl
SIDE BY SIDE DIFF: $sideBySideUrl
        ";
	}
	/**
	 * @return array
	 * @throws QMFileNotFoundException
	 */
	public function getLinks(): array{
		$inlineUrl = $this->getInlineUrl();
		$sideBySideUrl = $this->getSideBySideUrl();
		return [
			"INLINE DIFF" => $inlineUrl,
			"SIDE BY SIDE DIFF" => $sideBySideUrl,
			"View Actual:" => $this->getActualUrl(),
		];
	}
	public function getActualUrl(): string{
		if($this->actualUrl){
			return $this->actualUrl;
		}
		$url = S3Private::uploadHTML($this->fixturePath, $this->actualRaw, false);
		return $this->actualUrl = UrlHelper::getLocalUrl($url);
	}
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function saveIfUpdatingFixtures(): void{
		if(Env::get(Env::UPDATE_HTML_FIXTURES)){ // I think this causes infinite loops
			if(QMAPIRepo::branchIsLike(UpdateHtmlTestFixturesTest::FEATURE_UPDATED_HTML)){
				$this->save();
			}
		}
	}
	/**
	 * @param string $str
	 * @return array|string|string[]
	 */
	private static function replaceFeatureWithTestingUrl(string $str): string{
		$str = str_replace("https://feature.quantimo.do", "https://testing.quantimo.do", $str);
		return $str;
	}
}
