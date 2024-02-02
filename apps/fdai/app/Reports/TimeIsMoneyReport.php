<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports;
use App\Charts\BarChartButton;
use App\DataSources\Connectors\RescueTimeConnector;
use App\Mail\QMSendgrid;
use App\Models\Measurement;
use App\Models\WpPost;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\User\QMUser;
use App\Types\QMStr;
use App\UI\QMColor;
use App\Units\HoursUnit;
use Illuminate\View\View;
class TimeIsMoneyReport extends AnalyticalReport {
    public const TITLE = "Time Receipt";
    public const CSS_PATHS = [
    ];
    const CATEGORY_DESCRIPTION = "Analysis of how time is spent and it's monetary equivalent";
    /**
     * @return string
     */
    public function getTitleAttribute(): string{
        return "Factors Most Likely to Influence ";
    }
    /**
     * @return string
     */
    public function getCoverImage(): string{
        return RescueTimeConnector::IMAGE;
    }
    /**
     * @param QMMeasurement $m
     * @param int $absoluteMax
     * @return string
     */
    private function getTruncatedCauseName(QMMeasurement $m, int $absoluteMax = 45): string {
        $width = $this->getBarWidth($m);
        $maxChars = $absoluteMax * $width/100;
        $name = QMStr::truncate($m->getVariableName(), $maxChars);
        return $name;
    }
    /**
     * @return QMMeasurement[]
     */
    private function getMeasurements(): array {
        $rows = QMMeasurement::readonly()
            ->where(Measurement::FIELD_USER_ID, $this->getUserId())
            ->where(Measurement::FIELD_UNIT_ID, HoursUnit::ID)
            ->where(Measurement::FIELD_START_TIME, '>', time() - 86400)
            ->getArray();
        $measurements = QMMeasurement::instantiateArray($rows);
        return $measurements;
    }
    /**
     * @return void
     */
    public function generatePDF(): void {
        $this->addHtml($this->generateHtmlBodyWithInlineCss());
    }
    /**
     * @return int
     */
    private function getMaxChange(): int{
        $measurements = $this->getMeasurements();
        $max = 0;
        foreach ($measurements as $m) {
            if ($m->getValue() > $max) {
                $max = $m->getValue();
            }
        }
        return $max;
    }
    /**
     * @param QMMeasurement $m
     * @return float|int
     */
    private function getBarWidth(QMMeasurement $m){
        $max = $this->getMaxChange();
        $factor = 30 / $max;
        $value = $m->getValue();
        $width = $value * $factor;
        if ($width < 0) {
            $width = $width * -1;
        }
        $width = $width + 70;
        if($width > 100) {$width = 100;}
        return $width;
    }
    /**
     * @param int|null $recipientUserId
     * @return QMSendgrid
     */
    public function email(int $recipientUserId = null): QMSendgrid{
        if(!$recipientUserId){$recipientUserId = $this->getUserId();}
        $recipientUserId = UserIdProperty::USER_ID_MIKE;
        //$html = $this->getHtmlWithHead();
        $html = $this->generateHtmlBodyWithInlineCss();
        $email = new QMSendgrid($recipientUserId, $this->getTitleAttribute(), $html);
        $email->send();
        return $email;
    }
    /**
     * @return string
     */
    public function getHtmlWithTable(): string {
        $measurements = $this->getMeasurements();
        $html = "
            <div style=\"font-family: 'Source Sans Pro', sans-serif;\">";
        $html .= $this->getTitleDescriptionHeaderHtml();
        foreach($measurements as $m){
            $html .= $this->getTableRowBlock($m);
        }
        $html .= '
            </div>
        ';
        return $this->bodyHtml = $html;
    }
    /**
     * @param QMMeasurement $m
     * @return string
     */
    private function getTableRowBlock(QMMeasurement $m):string {
        $url = $m->getAdditionalMetaData()->url;
        $v = $m->getQMUserVariable();
        $toolTip = $v->getVariableName();
        $color = QMColor::HEX_LIGHT_BLUE;
        $width = $this->getBarWidth($m);
        $percent = $m->getValue();
        $causeName = $this->getTruncatedCauseName($m);
        $img = $m->getQMUserVariable()->getImageUrl();
        $html = BarChartButton::getHtmlWithRightText($causeName, $width, $url, $color, $img, $percent, $toolTip);
        return $html;
    }
    /**
     * @return string
     */
    public function generateBodyHtml(): string {
        return $this->getHtmlWithTable();
    }
    /**
     * @return string
     */
    public function generateHtmlBodyWithInlineCss(): string {
        return $this->getHtmlWithTable();
    }
    /**
     * @return array
     */
    public function getSpreadsheetRows(): array{
        // TODO: Implement getSpreadsheetRows() method.
    }
    /**
     * @return string
     */
    public function getSubtitleAttribute(): string{
        return "This is how much you spent yesterday assuming your time is worth $25/hour.";
    }
    /**
     * @return QMUser
     */
    public function getSourceObject(): QMUser{
        return $this->getQMUser();
    }
    /**
     * @inheritDoc
     */
    public function generateEmailBody(): string{
        // TODO: Implement generateEmailHtml() method.
    }
    /**
     * @inheritDoc
     */
    public function getCategoryDescription(): string{
        return self::CATEGORY_DESCRIPTION;
    }
    /**
     * @inheritDoc
     */
    public function getCategoryName(): string{
        return WpPost::CATEGORY_TIME_IS_MONEY_REPORTS;
    }
    /**
     * @return AnalyticalReport
     */
    static public function getDemoReport(): AnalyticalReport {
        return new self(UserIdProperty::USER_ID_MIKE);
    }
	public function getShowContentView(array $params = []): View{
		// TODO: Implement getShowContentView() method.
	}
	protected function getShowPageView(array $params = []): View{
		// TODO: Implement getShowPageView() method.
	}
}
