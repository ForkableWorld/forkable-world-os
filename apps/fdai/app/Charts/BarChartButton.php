<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection HtmlDeprecatedAttribute */
namespace App\Charts;
use App\Buttons\QMButton;
use App\Files\FileHelper;
use App\Slim\Model\QMUnit;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\UI\QMColor;
use App\Units\PercentUnit;
class BarChartButton extends QMButton {
	public const WP_IMAGE_HEIGHT = 48.0001;
	public const EMAIL_IMAGE_HEIGHT = 60; // It's too big in email if larger than 40, but it's too small in wordpress for some reason
	public const IMAGE_CSS_PLACEHOLDER = "__IMAGE_SIZE__";
	const MAX_HEIGHT = 60;
	public $leftText;
	public $textLeft;
	public $textRight;
	public $toolTip;
	public $unit;
	public $value;
	public $width;
	public float $widthPercent = 100;
	/**
	 * BarChartRow constructor.
	 * @param string|null $fullName
	 * @param int|null $widthPercent
	 * @param string|null $url
	 * @param string|null $backgroundColor
	 * @param string|null $img
	 * @param string|null $textRight
	 * @param string|null $toolTip
	 */
	public function __construct(string $fullName = null, float $widthPercent = null, string $url = null,
		string $backgroundColor = null, string $img = null, string $textRight = null, string $toolTip = null){
		$this->image = $img;
		$this->textLeft = $fullName;
		$this->textRight = $textRight;
		$this->toolTip = $toolTip;
		if($backgroundColor){
			$this->backgroundColor = $backgroundColor;
		}
		if($url){
			$this->setUrl($url);
		}
		if($widthPercent !== null){
			$absoluteMax = 98; // Gets cut off no matter what I do
			$fuckUpFactor = $absoluteMax / 100;
			$minWidth = 75;
			$available = $absoluteMax - $minWidth;
			$float = $fuckUpFactor * ($minWidth + $widthPercent * $available / 100);
			$this->widthPercent = (int) $float;
		}
	}
	/**
	 * @param string $textLeft
	 * @param float $widthPercent
	 * @param string $url
	 * @param string $color
	 * @param string $img
	 * @param string $textRight
	 * @param string $toolTip
	 * @return string
	 */
	public static function getHtmlWithRightText(string $textLeft, float $widthPercent, string $url, string $color,
		string $img, string $textRight, string $toolTip): string{

		$instance = new self($textLeft, $widthPercent, $url, $color, $img, $textRight, $toolTip);
		return $instance->getTableHtml();
	}
	public static function generateHtml(string $textLeft, string $url, string $img, string $toolTip,
		string $backgroundColor = null): string{
		$instance = new self($textLeft, null, $url, $backgroundColor, $img, null, $toolTip);
		return $instance->getTableHtml();
	}
	/**
	 * @param string $textLeft
	 * @param float $widthPercent
	 * @param string $url
	 * @param string $color
	 * @param string $img
	 * @param string $textRight
	 * @param string $toolTip
	 * @param array $params
	 * @return string
	 */
	public static function getPostFormButton(string $textLeft, float $widthPercent, string $url, string $color,
		string $img, string $textRight, string $toolTip, array $params): string{
		$fixed = false;
		if(!$fixed){
			le("FIX ME!");
		}
		$instance = new self($textLeft, $widthPercent, null, $color, $img, $textRight, $toolTip);
		$table = $instance->getTableHtml();
		$inputs = '';
		foreach($params as $name => $value){
			$inputs .= "<input type=\"hidden\" name=\"your_field_name[]\" value=\"1\" />\n";
		}
		$html = "
            <form method=\"POST\" ACTION=\"$url\">
                $table
                $inputs
                <input type=\"SUBMIT\">
            </form>";
		return $html;
	}
	/**
	 * @return string
	 */
	public function getTableHtml(): string{
		$rightText = $this->getTextRight();
		$leftText = $this->getTextLeft();
		$totalWidthPercent = $this->getWidthPercent();
		if($rightText){
			$rightLengthPlus3 = strlen($rightText) + 4; // 3 for spacing
			$truncatedLeft = QMStr::truncate($leftText, (45 - $rightLengthPlus3) * $totalWidthPercent / 100);
			$rightWidthPercent = round(70 * $rightLengthPlus3 / ($rightLengthPlus3 + strlen($truncatedLeft)));
			$leftWidthPercent = round(70 * strlen($truncatedLeft) / ($rightLengthPlus3 + strlen($truncatedLeft)));
		} else{
			$rightWidthPercent = 0;
			$leftWidthPercent = 80;
			$truncatedLeft = $leftText;
		}
		$img = $this->getImage();
		if($this->backgroundColor === "white"){
			$textColor = "black";
		} else{
			$textColor = "white";
		}
		$maxHeight = self::MAX_HEIGHT;
		$html = "";
		$heightBlock = "height: $maxHeight" . "px;
                            display: block;";
		$transparentTableBorderCss = 'border: 0 solid transparent; ';
		$id = QMStr::slugify($leftText);
		// Make sure to keep <a> the outermost wrapper so  resources/views/search-filter-script.blade.php works
		$html .= "
            <a href=\"$this->link\"
                title=\"$this->toolTip\"
                style=\"
                    -webkit-box-shadow: unset;
                    box-shadow: unset;
                    transition: unset;
                \"
            >
                <div class='$id-bar-chart-button'
                    style=\"width:98%; display:block; height: " . $maxHeight . "px; margin-bottom: 10px;\">
                    <div style=\"
                        width: $totalWidthPercent%;
                        max-height: " . $maxHeight . "px;
                        margin: 3px;
                        background-color: $this->backgroundColor;
                        $transparentTableBorderCss
                        border-radius:" . $maxHeight . "px;
                        display:inline-block;
                        font-size:16px;
                        text-align:left;
                        text-decoration:none;
                        -webkit-text-size-adjust:none;
                        mso-hide:all;\">
                    <table
                        border=\"0\" cellspacing=\"0\" cellpadding=\"0\"
                        style='
                            padding: 0;
                            border-collapse: collapse; border-spacing: 0;
                            width: 100%;
                            border: 0 solid transparent;
                            margin: 0;
                            $heightBlock
                        '>
                        <tbody style='$heightBlock $transparentTableBorderCss'>
                            <tr style=\"
                                color:$textColor;
                                $heightBlock
                                $transparentTableBorderCss
                                \">	";
		$html .= $this->getImageCell($transparentTableBorderCss, $id, $img);
		$html .= $this->getLeftTextCell($textColor, $leftWidthPercent, $transparentTableBorderCss, $truncatedLeft);
		if($rightText){
			$html .= $this->getRightCell($rightWidthPercent, $textColor, $transparentTableBorderCss, $rightText);
		}
		$html .= "

                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </a>
        ";
		return $html;
	}
	/**
	 * @return string
	 */
	public function getTextLeft(): string{
		return $this->textLeft;
	}
	/**
	 * @param string $text
	 * @return static
	 */
	public function setTextAndTitle(string $text){
		$this->setTextLeft($text);
		return parent::setTextAndTitle($text);
	}
	/**
	 * @param string $textLeft
	 */
	public function setTextLeft(string $textLeft): void{
		$this->textLeft = $textLeft;
	}
	/**
	 * @return int
	 */
	public function getWidthPercent(): int{
		return (int)$this->widthPercent;
	}
	/**
	 * @return string
	 */
	public function getTextRight(): ?string{
		return $this->textRight;
	}
	/**
	 * @param string $textRight
	 */
	public function setTextRight(string $textRight): void{
		$this->textRight = $textRight;
	}
	/**
	 * @return string
	 */
	public static function getTestHtml(): string{
		$textLeft = "Left Text";
		$url = "https://web.quantimo.do/#/app/variable-settings";
		$img = "https://static.quantimo.do/img/Ionicons/png/512/settings.png";
		$img = "https://static.quantimo.do/img/rating/google-colors/100/face_rating_button_100_ecstatic.png";
		$color = QMColor::HEX_DARK_GRAY;
		$toolTip = "Tooltip";
		//return BarChartTableRowForEmail::getHtml($textLeft, $url, $img, $toolTip);
		$instance = new self($textLeft, 75, $url, $color, $img, "Right Text", $toolTip);
		return $instance->getTableHtml();
	}
	public static function convertToEmailHtml(string $withPlaceHolder): string{
		$emailHtml = str_replace(self::IMAGE_CSS_PLACEHOLDER, self::EMAIL_IMAGE_HEIGHT, $withPlaceHolder);
		return $emailHtml;
	}
	public static function convertToWpHtml(string $withPlaceHolder): string{
		$wp = str_replace(self::EMAIL_IMAGE_HEIGHT, self::WP_IMAGE_HEIGHT, $withPlaceHolder);
		return $wp;
	}
	/**
	 * @param $rightWidthPercent
	 * @param string $textColor
	 * @param string $transparentTableBorderCss
	 * @param string|null $rightText
	 * @return string
	 */
	private function getRightCell($rightWidthPercent, string $textColor, string $transparentTableBorderCss,
		?string $rightText): string{
		$maxHeight = $this->getInnerHeight();
		$html = "
                                <td style=\"
                                        position: relative;
                                        text-align: right;
                                        padding: 0;
                                        margin: 0 3px 0 0;
                                        width: " . $rightWidthPercent . "%;
                                        color: $textColor;
                                        $transparentTableBorderCss
                                        height: $maxHeight" . "px;
                                        display: inline-block;
                                        float: right;
                                    \">
                                    <div style=\"
                                        display: table;
                                        height: $maxHeight" . "px;
                                        overflow: hidden;
                                        width: 100%;
                                        \">
                                      <div style=\"display: table-cell; vertical-align: middle;\">
                                        <div style='padding-right: 6px;'>
                                          $rightText &nbsp;
                                        </div>
                                      </div>
                                    </div>
                                 </td>
                                 ";
		return $html;
	}
	/**
	 * @param string $textColor
	 * @param $leftWidthPercent
	 * @param string $transparentTableBorderCss
	 * @param string $truncatedLeft
	 * @return string
	 */
	private function getLeftTextCell(string $textColor, $leftWidthPercent, string $transparentTableBorderCss,
		string $truncatedLeft): string{
		$maxHeight = self::MAX_HEIGHT;
		$maxHeight = $this->getInnerHeight();
		$html = "
                                <td style='
                                        padding: 0;
                                        color: $textColor;
                                        width: $leftWidthPercent%;
                                        $transparentTableBorderCss
                                        height: $maxHeight" . "px;
                                        display: inline-block;
                                    '>
                                    <div style=\"
                                        display: table;
                                        height: $maxHeight" . "px;
                                        overflow: hidden;
                                        width: 100%;
                                        \">
                                      <div style=\"display: table-cell; vertical-align: middle; height: $maxHeight" . "px; \">
                                        <div style='padding-left: 3px;'>
                                          $truncatedLeft
                                        </div>
                                      </div>
                                    </div>
                                </td>";
		return $html;
	}
	/**
	 * @param string $transparentTableBorderCss
	 * @param string $id
	 * @param string $img
	 * @return string
	 */
	private function getImageCell(string $transparentTableBorderCss, string $id, string $img): string{
		$innerHeight = self::EMAIL_IMAGE_HEIGHT;
		$imageSize = 40;
		$max = self::MAX_HEIGHT;
		$html = "
                                <td
                                    align=\"center\"
                                    height=\"$innerHeight\" width=\"$innerHeight\"
                                    style='
                                        padding: 0;
                                        object-fit: contain;
                                        width: " . $innerHeight . "px;
                                        border-radius: " . $innerHeight . "px;
                                        $transparentTableBorderCss
                                        height: $innerHeight" . "px;
                                        display: inline-block;
                                        vertical-align: top;" .
			// Top required, or it puts the images low on study pages for some reason
			// https://web.quantimo.do/#/app/study?causeVariableName=Potassium%2520%2528%2525RDA%2529&effectVariableName=Overall%2520Mood&userId=230&studyId=cause-1463001-effect-1398-user-230-user-study
			// Looks bad on PDF, but it already looked stupid anyway in PDF (https://local.quantimo.do/pdf)
			"
                                    '>
                                      <div style=\"
                                          width: " . $max . "px;
                                                        height: " . $max . "px;
                                          border-radius: 50%;
                                                  border: 6px solid $this->backgroundColor;
                                        box-shadow: inset 0 0 0 10px white;
                                        box-sizing: border-box; /* Include padding and border in element's width and height */
                                      \">
                                                <img
                                                    height=\"$imageSize\" width=\"$imageSize\"
                                                    class='$id-image'
                                                    src=\"$img\"
                                                    alt=\"$this->textLeft\"
                                                    style=\"
                                                        border: 4px solid white;
                                                        background-color: white;
                                                        width: " . $imageSize . "px;
                                                        height: " . $imageSize . "px;
                                                        border-radius: " . $imageSize . "px;
                                                        box-sizing: content-box;
                                                    \">
                                      </div>
                                </td>	";
		return $html;
	}
	/**
	 * @return float|int
	 */
	public function getInnerHeight(){
		$padding = 1;
		$innerHeight = self::MAX_HEIGHT - 2 * $padding;
		return $innerHeight;
	}
	/**
	 * @return int
	 */
	public function getWidth(): int{
		return $this->width;
	}
	/**
	 * @param int $width
	 */
	public function setWidth(int $width): void{
		$this->width = $width;
	}
	public function getChipSmall(): string{
		return HtmlHelper::renderView(view('correlation-chip', ['button' => $this]));
	}
	public function getBarWithImage(): string{
		return $this->getTableHtml();
	}
	/**
	 * @return float
	 */
	public function getValue(): float{
		return $this->value;
	}
	/**
	 * @param float $value
	 */
	public function setValue(float $value): void{
		$this->value = $value;
	}
	/**
	 * @return mixed
	 */
	public function getUnit(): QMUnit{
		if(!$u = $this->unit){
			return PercentUnit::instance();
		}
		return $this->unit;
	}
	/**
	 * @param mixed $unit
	 */
	public function setUnit($unit): void{
		$this->unit = $unit;
	}
	/**
	 * @return mixed
	 */
	public function getLeftText(): string{
		return $this->leftText;
	}
	/**
	 * @param mixed $leftText
	 */
	public function setLeftText($leftText): void{
		$this->leftText = $leftText;
	}
	public function getFullWidthBar(): string{
		return view('full-width-bar', ['button' => $this]);
	}
	/**
	 * @param string $title
	 * @param static[] $buttons
	 * @return string
	 */
	public static function renderFullWidthBarChart(string $title, array $buttons): string{
		QMArr::sortDescending($buttons, 'widthPercent');
		return HtmlHelper::renderView(view('bar-chart-full-width', [
			'buttons' => $buttons,
			'title' => $title,
			'searchId' => QMStr::slugify($title),
		]));
	}
	/**
	 * @param string $title
	 * @param static[] $buttons
	 * @param string $placeholder
	 * @return string
	 */
	public static function renderImagesBarChart(string $title, array $buttons, string $placeholder): string{
		QMArr::sortDescending($buttons, 'widthPercent');
		return HtmlHelper::renderView(view('bar-chart-with-images', [
			'buttons' => $buttons,
			'title' => $title,
			'searchId' => QMStr::slugify($title),
			'placeholder' => $placeholder,
		]));
	}
}
